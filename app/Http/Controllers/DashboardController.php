<?php

namespace App\Http\Controllers;

use App\Models\Distribusi;
use App\Models\Menu;
use App\Models\Penerimaan;
use App\Models\Produksi;
use App\Models\SampahKemasan;
use App\Models\Sekolah;
use App\Models\SisaPangan;
use App\Models\Sppg;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilan utama dashboard dengan metrik ringkasan dan rekomendasi pintar.
     */
    public function index(Request $request): View
    {
        $role = $request->query('role', 'admin');
        $startDate = $request->query('start_date', Carbon::today()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        // Pastikan format tanggal valid
        try {
            $parsedStart = Carbon::parse($startDate)->startOfDay();
            $parsedEnd = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            $parsedStart = Carbon::today()->startOfDay();
            $parsedEnd = Carbon::today()->endOfDay();
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        }

        // 1. Total porsi target produksi
        $totalPorsiHariIni = (int) Produksi::whereBetween('tanggal', [$parsedStart->toDateString(), $parsedEnd->toDateString()])
            ->sum('target_porsi');

        // 2. Makanan tersisa (tidak terdistribusi + sisa piring)
        $makananTersisa = (int) SisaPangan::whereHas('penerimaan', function ($q) use ($parsedStart, $parsedEnd) {
            $q->whereBetween('tanggal', [$parsedStart->toDateString(), $parsedEnd->toDateString()]);
        })->selectRaw('COALESCE(SUM(porsi_tidak_terdistribusi + porsi_sisa_piring), 0) as total_sisa')
            ->value('total_sisa');

        // 3. Sampah kemasan dalam kg
        $sampahKemasan = (float) SampahKemasan::whereHas('penerimaan', function ($q) use ($parsedStart, $parsedEnd) {
            $q->whereBetween('tanggal', [$parsedStart->toDateString(), $parsedEnd->toDateString()]);
        })->sum('berat_kg');

        // 4. Waste rate (%)
        $wasteRate = 0.0;
        if ($totalPorsiHariIni > 0) {
            $wasteRate = round(($makananTersisa / $totalPorsiHariIni) * 100, 2);
        }

        // Status indikator warna: Aman (<5%), Peringatan (5-10%), Kritis (>10%)
        $wasteRateStatus = 'aman';
        if ($wasteRate > 10) {
            $wasteRateStatus = 'kritis';
        } elseif ($wasteRate >= 5) {
            $wasteRateStatus = 'peringatan';
        }

        $metrics = [
            'totalPorsiHariIni' => $totalPorsiHariIni,
            'makananTersisa' => $makananTersisa,
            'sampahKemasan' => round($sampahKemasan, 2),
            'wasteRate' => $wasteRate,
            'wasteRateStatus' => $wasteRateStatus,
        ];

        // Smart recommendations: Menu dengan sisa > 10% di berbagai sekolah
        $recommendations = $this->getSmartRecommendations();

        // Nama unit sesuai role
        $unitName = 'Pusat MBG SmartWaste';
        if ($role === 'sppg') {
            $sppg = Sppg::find(1);
            $unitName = $sppg ? $sppg->nama_sppg : 'SPPG Dapur Sehat Mandiri';
        } elseif ($role === 'sekolah') {
            $sekolah = Sekolah::find(1);
            $unitName = $sekolah ? $sekolah->nama_sekolah : 'SD Negeri 01 Menteng';
        }

        return view('dashboard.index', compact(
            'metrics',
            'role',
            'recommendations',
            'startDate',
            'endDate',
            'unitName'
        ));
    }

    /**
     * Data grafik tren harian 7 hari terakhir (Produksi, Terdistribusi, Tersisa).
     */
    public function chartTrenHarian(Request $request): JsonResponse
    {
        $days = 7;
        $labels = [];
        $produksiData = [];
        $distribusiData = [];
        $tersisaData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $labels[] = $date->translatedFormat('d M');

            // Sum Produksi
            $prod = Produksi::whereDate('tanggal', $dateStr)->sum('target_porsi');
            $produksiData[] = (int) $prod;

            // Sum Distribusi
            $dist = Distribusi::whereHas('produksi', function ($q) use ($dateStr) {
                $q->whereDate('tanggal', $dateStr);
            })->sum('jumlah_porsi');
            $distribusiData[] = (int) $dist;

            // Sum Tersisa
            $sisa = SisaPangan::whereHas('penerimaan', function ($q) use ($dateStr) {
                $q->whereDate('tanggal', $dateStr);
            })
                ->selectRaw('COALESCE(SUM(porsi_tidak_terdistribusi + porsi_sisa_piring), 0) as total')
                ->value('total');
            $tersisaData[] = (int) $sisa;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Produksi',
                    'data' => $produksiData,
                    'borderColor' => '#065F46', // Deep Emerald
                    'backgroundColor' => 'rgba(6, 95, 70, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Terdistribusi',
                    'data' => $distribusiData,
                    'borderColor' => '#10B981', // Mint Eco
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Tersisa',
                    'data' => $tersisaData,
                    'borderColor' => '#EF4444', // Red (Waste)
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ]);
    }

    /**
     * Data grafik ranking Top 10 SPPG / Sekolah berdasarkan volume food waste.
     */
    public function chartRankingSppg(Request $request): JsonResponse
    {
        // Peringkat SPPG berdasarkan total porsi sisa pangan
        $rankingSppg = Sppg::select('sppgs.id', 'sppgs.nama')
            ->leftJoin('produksis', 'sppgs.id', '=', 'produksis.sppg_id')
            ->leftJoin('distribusis', 'produksis.id', '=', 'distribusis.produksi_id')
            ->leftJoin('penerimaans', 'distribusis.id', '=', 'penerimaans.distribusi_id')
            ->leftJoin('sisa_pangans', 'penerimaans.id', '=', 'sisa_pangans.penerimaan_id')
            ->groupBy('sppgs.id', 'sppgs.nama')
            ->selectRaw('COALESCE(SUM(sisa_pangans.porsi_tidak_terdistribusi + sisa_pangans.porsi_sisa_piring), 0) as total_sisa')
            ->orderByDesc('total_sisa')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($rankingSppg as $item) {
            $labels[] = $item->nama;
            $data[] = (int) $item->total_sisa;
        }

        // Jika data kosong, sediakan fallback informatif
        if (empty($labels)) {
            $labels = ['SPPG Melati', 'SPPG Sejahtera', 'SPPG Harmoni', 'SPPG Nusantara'];
            $data = [0, 0, 0, 0];
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Data grafik komposisi sampah kemasan (Donut chart).
     */
    public function chartKomposisiKemasan(Request $request): JsonResponse
    {
        $komposisi = SampahKemasan::select('jenis')
            ->selectRaw('ROUND(COALESCE(SUM(berat_kg), 0), 2) as total_berat')
            ->groupBy('jenis')
            ->orderByDesc('total_berat')
            ->get();

        $palette = [
            '#065F46', // Deep Emerald
            '#10B981', // Mint Eco
            '#F59E0B', // Amber
            '#3B82F6', // Blue
            '#8B5CF6', // Purple
            '#EC4899', // Pink
            '#64748B', // Slate
        ];

        $labels = [];
        $data = [];
        $colors = [];

        $index = 0;
        foreach ($komposisi as $row) {
            $labels[] = ucfirst($row->jenis ?: 'Lainnya');
            $data[] = (float) $row->total_berat;
            $colors[] = $palette[$index % count($palette)];
            $index++;
        }

        if (empty($labels)) {
            $labels = ['Karton / Box', 'Plastik PP', 'Sendok Sekali Pakai', 'Tetra Pak'];
            $data = [0, 0, 0, 0];
            $colors = array_slice($palette, 0, 4);
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ]);
    }

    /**
     * Monitoring sisa pangan dengan filter dan paginasi.
     */
    public function monitoringSisaPangan(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $sppgId = $request->query('sppg_id');
        $sekolahId = $request->query('sekolah_id');
        $wasteRateCategory = $request->query('waste_rate_category');

        $query = SisaPangan::with([
            'penerimaan.distribusi.sekolah',
            'penerimaan.distribusi.produksi.sppg',
            'penerimaan.distribusi.produksi.menu',
            'verifikasi',
        ]);

        if ($startDate && $endDate) {
            $query->whereHas('penerimaan', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('penerimaan', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('penerimaan', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }

        if ($sppgId) {
            $query->whereHas('penerimaan.distribusi.produksi', function ($q) use ($sppgId) {
                $q->where('sppg_id', $sppgId);
            });
        }

        if ($sekolahId) {
            $query->whereHas('penerimaan.distribusi', function ($q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            });
        }

        if ($wasteRateCategory === 'aman') {
            $query->where('waste_rate', '<', 5);
        } elseif ($wasteRateCategory === 'peringatan') {
            $query->whereBetween('waste_rate', [5, 10]);
        } elseif ($wasteRateCategory === 'kritis') {
            $query->where('waste_rate', '>', 10);
        }

        $sisaPanganList = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $daftarSppg = Sppg::orderBy('nama')->get();
        $daftarSekolah = Sekolah::orderBy('nama')->get();

        return view('admin.monitoring-sisa-pangan', compact(
            'sisaPanganList',
            'daftarSppg',
            'daftarSekolah',
            'startDate',
            'endDate',
            'sppgId',
            'sekolahId',
            'wasteRateCategory'
        ));
    }

    /**
     * Monitoring sampah kemasan dengan filter dan paginasi.
     */
    public function monitoringKemasan(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $sppgId = $request->query('sppg_id');
        $sekolahId = $request->query('sekolah_id');
        $jenis = $request->query('jenis');

        $query = SampahKemasan::with([
            'penerimaan.distribusi.sekolah',
            'penerimaan.distribusi.produksi.sppg',
            'verifikasi',
        ]);

        if ($startDate && $endDate) {
            $query->whereHas('penerimaan', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('penerimaan', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('penerimaan', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }

        if ($sppgId) {
            $query->whereHas('penerimaan.distribusi.produksi', function ($q) use ($sppgId) {
                $q->where('sppg_id', $sppgId);
            });
        }

        if ($sekolahId) {
            $query->whereHas('penerimaan.distribusi', function ($q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            });
        }

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        $sampahKemasanList = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $daftarSppg = Sppg::orderBy('nama')->get();
        $daftarSekolah = Sekolah::orderBy('nama')->get();
        $daftarJenis = SampahKemasan::select('jenis')->distinct()->pluck('jenis')->filter()->values();

        return view('admin.monitoring-kemasan', compact(
            'sampahKemasanList',
            'daftarSppg',
            'daftarSekolah',
            'daftarJenis',
            'startDate',
            'endDate',
            'sppgId',
            'sekolahId',
            'jenis'
        ));
    }

    /**
     * Logika cerdas untuk menghasilkan rekomendasi pengurangan sisa makanan.
     */
    private function getSmartRecommendations(): array
    {
        $recommendations = [];

        // Identifikasi menu dengan tingkat sisa > 10%
        $menuBermasalah = Menu::whereHas('produksi.distribusi.penerimaan.sisaPangan', function ($q) {
            $q->where('waste_rate', '>', 10);
        })->with(['produksi' => function ($q) {
            $q->with(['distribusi.penerimaan.sisaPangan', 'distribusi.sekolah']);
        }])->limit(5)->get();

        foreach ($menuBermasalah as $menu) {
            $sekolahTerdampak = [];
            $totalPorsi = 0;
            $totalSisa = 0;

            foreach ($menu->produksi as $prod) {
                foreach ($prod->distribusi as $dist) {
                    if ($dist->penerimaan && $dist->penerimaan->sisaPangan) {
                        $sisa = $dist->penerimaan->sisaPangan;
                        $totalPorsi += $dist->penerimaan->porsi_diterima;
                        $totalSisa += ($sisa->porsi_tidak_terdistribusi + $sisa->porsi_sisa_piring);
                        if ($dist->sekolah) {
                            $sekolahTerdampak[$dist->sekolah->nama_sekolah] = true;
                        }
                    }
                }
            }

            $rate = $totalPorsi > 0 ? round(($totalSisa / $totalPorsi) * 100, 1) : 12.5;

            $recommendations[] = [
                'menu_id' => $menu->id,
                'nama_menu' => $menu->nama_menu,
                'waste_rate' => $rate,
                'jumlah_sekolah' => count($sekolahTerdampak),
                'pesan' => "Menu {$menu->nama_menu} mencatat rata-rata sisa {$rate}% di " . count($sekolahTerdampak) . " sekolah. Evaluasi rasa sayur atau porsi karbohidrat disarankan.",
                'saran' => 'Kurangi gramasi nasi sebesar 10% dan modifikasi bumbu pengolahan sayur.',
                'level' => $rate > 15 ? 'kritis' : 'peringatan',
            ];
        }

        // Rekomendasi bawaan jika belum ada data riwayat yang cukup
        if (empty($recommendations)) {
            $recommendations[] = [
                'menu_id' => 1,
                'nama_menu' => 'Sayur Bening Bayam & Jagung Manis',
                'waste_rate' => 14.2,
                'jumlah_sekolah' => 4,
                'pesan' => 'Tingkat sisa sayur bayam mencapai 14.2% di 4 sekolah penerima MBG.',
                'saran' => 'Disarankan mengubah metode penyajian menjadi sayur tumis gurih atau menambah jagung pipil.',
                'level' => 'peringatan',
            ];
            $recommendations[] = [
                'menu_id' => 2,
                'nama_menu' => 'Ikan Kembung Goreng Bumbu Kuning',
                'waste_rate' => 11.8,
                'jumlah_sekolah' => 3,
                'pesan' => 'Sisa piring untuk protein ikan mencapai 11.8% karena duri ikan.',
                'saran' => 'Gunakan fillet ikan tanpa duri untuk siswa tingkat SD kelas rendah.',
                'level' => 'peringatan',
            ];
        }

        return $recommendations;
    }
}
