@extends('layouts.app')

@section('title', 'Executive Dashboard MBG SmartWaste')

@section('content')
<div class="space-y-6" x-data="dashboardData()">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Sistem Pemantauan Nasional
                </span>
                <span class="text-xs text-slate-500">• Terakhir diperbarui: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Dasbor Pemantauan MBG SmartWaste</h1>
            <p class="text-sm text-slate-600">Pelacakan efisiensi produksi, distribusi pangan bergizi, dan reduksi sampah kemasan terpadu.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                <button type="button" @click="setPeriod('today')" :class="period === 'today' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                    Hari Ini
                </button>
                <button type="button" @click="setPeriod('week')" :class="period === 'week' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                    7 Hari Terakhir
                </button>
                <button type="button" @click="setPeriod('month')" :class="period === 'month' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                    Bulan Ini
                </button>
            </div>
            <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Laporan Eksekutif
            </a>
        </div>
    </div>

    <!-- Top Section: 4 Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-metric-card 
            title="Total Porsi Hari Ini" 
            value="{{ number_format($metrics['totalPorsi'] ?? 14250) }}" 
            unit="porsi" 
            icon="fire" 
            color="emerald" 
        />
        
        <x-metric-card 
            title="Makanan Tersisa" 
            value="{{ number_format($metrics['makananTersisa'] ?? 485) }}" 
            unit="porsi" 
            icon="exclamation-triangle" 
            color="amber" 
        />
        
        <x-metric-card 
            title="Sampah Kemasan" 
            value="{{ number_format($metrics['sampahKemasan'] ?? 86.4, 1) }}" 
            unit="kg" 
            icon="archive-box" 
            color="sky" 
        />
        
        <x-metric-card 
            title="Waste Rate" 
            value="{{ number_format($metrics['wasteRate'] ?? 3.40, 2) }}" 
            unit="%" 
            icon="chart-pie" 
            color="{{ ($metrics['wasteRate'] ?? 3.40) < 5 ? 'emerald' : (($metrics['wasteRate'] ?? 3.40) <= 10 ? 'amber' : 'red') }}"
        >
            <div class="mt-2">
                <x-waste-badge :rate="$metrics['wasteRate'] ?? 3.40" />
            </div>
        </x-metric-card>
    </div>

    <!-- Smart Recommendation Banner -->
    @if(isset($recommendations) && count($recommendations) > 0)
        <div class="space-y-3">
            @foreach($recommendations as $rec)
                <x-alert-banner 
                    :type="is_array($rec) ? ($rec['type'] ?? 'warning') : 'warning'" 
                    :message="is_array($rec) ? ($rec['message'] ?? '') : $rec" 
                />
            @endforeach
        </div>
    @else
        <div class="space-y-3">
            <x-alert-banner 
                type="warning" 
                message="Peringatan: Terjadi lonjakan sisa makanan 15% pada menu Ikan Tongkol Balado di 4 sekolah mitra (SDN 01 Merdeka, SDN 04 Pagi, SMPN 02, SDN 05). Disarankan evaluasi tingkat kepedasan bumbu dan penyesuaian porsi protein nabati." 
            />
            <x-alert-banner 
                type="info" 
                message="Rekomendasi Efisiensi: 89% sampah kemasan karton di Wilayah Jakarta Pusat telah berhasil disalurkan ke Bank Sampah binaan untuk daur ulang sirkular." 
            />
        </div>
    @endif

    <!-- Charts Section (2 Columns Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Line Chart - Tren Harian (col-span-2) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Tren Produksi vs Distribusi vs Sisa Harian</h2>
                    <p class="text-xs text-slate-500">Perbandingan volume porsi terencana, porsi terdistribusi, dan akumulasi sisa makanan harian</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Produksi
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Distribusi
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Sisa
                    </span>
                </div>
            </div>

            <div class="relative w-full h-80 pt-4">
                <canvas id="trendChart"></canvas>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Rata-rata Tingkat Keberhasilan Distribusi: <strong class="text-emerald-700 font-semibold">98.2%</strong></span>
                <span>Ambang Batas Maksimal Sisa: <strong class="text-rose-600 font-semibold">5.0%</strong></span>
            </div>
        </div>

        <!-- Right Column: Stacked Charts (col-span-1) -->
        <div class="space-y-6">
            <!-- 1. Bar Chart - Ranking SPPG -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Peringkat Sisa Makanan per SPPG</h2>
                        <p class="text-xs text-slate-500">SPPG dengan volume sisa makanan tertinggi minggu ini</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">Watchlist</span>
                </div>
                <div class="relative w-full h-48 pt-3">
                    <canvas id="rankingSppgChart"></canvas>
                </div>
            </div>

            <!-- 2. Donut Chart - Komposisi Kemasan -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Komposisi Jenis Sampah Kemasan</h2>
                        <p class="text-xs text-slate-500">Distribusi berat material kemasan terkumpul (kg)</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200">Sirkular</span>
                </div>
                <div class="relative w-full h-48 pt-3">
                    <canvas id="komposisiKemasanChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent Activity / Verifikasi Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Aktivitas Verifikasi Laporan Terkini</h2>
                <p class="text-xs text-slate-500">10 data rekonsiliasi pangan harian terakhir yang dilaporkan oleh sekolah dan SPPG</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.verifikasi.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 hover:underline">
                    Lihat Antrean Lengkap
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3">ID Laporan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Sekolah Mitra</th>
                        <th class="px-5 py-3">SPPG Produsen</th>
                        <th class="px-5 py-3">Menu</th>
                        <th class="px-5 py-3 text-right">Porsi Diterima</th>
                        <th class="px-5 py-3 text-right">Sisa (Porsi)</th>
                        <th class="px-5 py-3 text-center">Waste Rate</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($recentVerifikasi ?? [
                        [
                            'id' => 'VRF-2026-0891',
                            'tanggal' => now()->format('Y-m-d'),
                            'sekolah' => 'SDN 01 Menteng Jaya',
                            'sppg' => 'SPPG Sentra Sejahtera 01',
                            'menu' => 'Ayam Suwir Madu, Sayur Sop, Semangka',
                            'porsi_diterima' => 450,
                            'sisa_porsi' => 12,
                            'waste_rate' => 2.67,
                            'status' => 'terverifikasi',
                        ],
                        [
                            'id' => 'VRF-2026-0890',
                            'tanggal' => now()->format('Y-m-d'),
                            'sekolah' => 'SMPN 15 Harmoni',
                            'sppg' => 'SPPG Bina Gizi Mandiri',
                            'menu' => 'Ikan Tongkol Balado, Lalapan, Melon',
                            'porsi_diterima' => 620,
                            'sisa_porsi' => 84,
                            'waste_rate' => 13.55,
                            'status' => 'perlu_tindak_lanjut',
                        ],
                        [
                            'id' => 'VRF-2026-0889',
                            'tanggal' => now()->format('Y-m-d'),
                            'sekolah' => 'SDN Cikini Terpadu',
                            'sppg' => 'SPPG Berkah Nutrisi Sehat',
                            'menu' => 'Rolade Daging Sapi, Capcay, Pisang',
                            'porsi_diterima' => 380,
                            'sisa_porsi' => 28,
                            'waste_rate' => 7.37,
                            'status' => 'menunggu_verifikasi',
                        ],
                        [
                            'id' => 'VRF-2026-0888',
                            'tanggal' => now()->subDay()->format('Y-m-d'),
                            'sekolah' => 'SDN 04 Kebon Sirih',
                            'sppg' => 'SPPG Sentra Sejahtera 01',
                            'menu' => 'Nasi Kuning Komplit, Telur Balado',
                            'porsi_diterima' => 510,
                            'sisa_porsi' => 15,
                            'waste_rate' => 2.94,
                            'status' => 'terverifikasi',
                        ],
                        [
                            'id' => 'VRF-2026-0887',
                            'tanggal' => now()->subDay()->format('Y-m-d'),
                            'sekolah' => 'SMPN 02 Salemba',
                            'sppg' => 'SPPG Dapur Utama Patriot',
                            'menu' => 'Semur Ayam, Tumis Buncis Jagung, Jeruk',
                            'porsi_diterima' => 700,
                            'sisa_porsi' => 31,
                            'waste_rate' => 4.43,
                            'status' => 'terverifikasi',
                        ]
                    ] as $item)
                        @php
                            $rate = is_array($item) ? $item['waste_rate'] : $item->waste_rate;
                            $status = is_array($item) ? $item['status'] : $item->status;
                            $id = is_array($item) ? $item['id'] : $item->id;
                            $sekolah = is_array($item) ? $item['sekolah'] : ($item->sekolah->nama ?? 'Sekolah');
                            $sppg = is_array($item) ? $item['sppg'] : ($item->sppg->nama ?? 'SPPG');
                            $menu = is_array($item) ? $item['menu'] : ($item->menu->nama ?? '-');
                            $porsi = is_array($item) ? $item['porsi_diterima'] : $item->porsi_diterima;
                            $sisa = is_array($item) ? $item['sisa_porsi'] : $item->sisa_porsi;
                            $tanggal = is_array($item) ? $item['tanggal'] : $item->tanggal;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-mono font-bold text-slate-800">{{ $id }}</td>
                            <td class="px-5 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-slate-900 font-semibold">{{ $sekolah }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $sppg }}</td>
                            <td class="px-5 py-3 max-w-xs truncate text-slate-700" title="{{ $menu }}">{{ $menu }}</td>
                            <td class="px-5 py-3 text-right font-mono tabular-nums">{{ number_format($porsi) }}</td>
                            <td class="px-5 py-3 text-right font-mono tabular-nums text-slate-900">{{ number_format($sisa) }}</td>
                            <td class="px-5 py-3 text-center">
                                <x-waste-badge :rate="$rate" />
                            </td>
                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                @if($status === 'terverifikasi')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Terverifikasi
                                    </span>
                                @elseif($status === 'menunggu_verifikasi')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                        Perlu Tindak Lanjut
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('admin.verifikasi.index') }}" class="inline-flex items-center text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada aktivitas verifikasi baru saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        period: 'today',
        setPeriod(p) {
            this.period = p;
        }
    };
}

window.addEventListener('DOMContentLoaded', function () {
    // 1. Line Chart: Tren Produksi vs Distribusi vs Sisa Harian
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        fetch('/api/chart/tren-harian')
            .then(res => {
                if (!res.ok) throw new Error('API offline');
                return res.json();
            })
            .then(data => {
                renderTrendChart(data.labels, data.produksi, data.distribusi, data.sisa);
            })
            .catch(() => {
                // Mock Fallback Data
                const labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Hari Ini'];
                const produksi = [13500, 14000, 13800, 14200, 14100, 14300, 14250];
                const distribusi = [13450, 13950, 13700, 14100, 14050, 14200, 14180];
                const sisa = [420, 390, 510, 440, 380, 490, 485];
                renderTrendChart(labels, produksi, distribusi, sisa);
            });
    }

    function renderTrendChart(labels, produksi, distribusi, sisa) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Produksi (Porsi)',
                        data: produksi,
                        borderColor: '#10B981', // Mint Eco / Emerald
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#10B981'
                    },
                    {
                        label: 'Distribusi (Porsi)',
                        data: distribusi,
                        borderColor: '#3B82F6', // Blue
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#3B82F6'
                    },
                    {
                        label: 'Sisa Makanan (Porsi)',
                        data: sisa,
                        borderColor: '#F43F5E', // Rose / Red
                        backgroundColor: 'rgba(244, 63, 94, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#F43F5E'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            color: '#64748B',
                            font: { size: 11 },
                            callback: function (val) {
                                return val >= 1000 ? (val / 1000) + 'k' : val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Bar Chart - Ranking SPPG (Horizontal)
    const rankingCtx = document.getElementById('rankingSppgChart');
    if (rankingCtx) {
        fetch('/api/chart/ranking-sppg')
            .then(res => {
                if (!res.ok) throw new Error('API offline');
                return res.json();
            })
            .then(data => {
                renderRankingChart(data.labels, data.values);
            })
            .catch(() => {
                const labels = ['SPPG Bina Mandiri', 'SPPG Dapur Utama', 'SPPG Sentra Sehat', 'SPPG Nutrisi Barokah', 'SPPG Berkah 03'];
                const values = [8.4, 6.2, 4.8, 3.9, 2.7];
                renderRankingChart(labels, values);
            });
    }

    function renderRankingChart(labels, values) {
        new Chart(rankingCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Waste Rate (%)',
                    data: values,
                    backgroundColor: values.map(v => v > 10 ? '#EF4444' : (v >= 5 ? '#F59E0B' : '#10B981')),
                    borderRadius: 6,
                    barThickness: 14
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ` Waste Rate: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F8FAFC' },
                        ticks: {
                            callback: val => val + '%',
                            font: { size: 10 },
                            color: '#64748B'
                        },
                        max: 15
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#334155' }
                    }
                }
            }
        });
    }

    // 3. Donut Chart - Komposisi Sampah Kemasan
    const komposisiCtx = document.getElementById('komposisiKemasanChart');
    if (komposisiCtx) {
        fetch('/api/chart/komposisi-kemasan')
            .then(res => {
                if (!res.ok) throw new Error('API offline');
                return res.json();
            })
            .then(data => {
                renderKomposisiChart(data.labels, data.values);
            })
            .catch(() => {
                const labels = ['Karton Ramah Lingkungan', 'Plastik Pembungkus', 'Mika Sekat', 'Sendok Bio-Plastik'];
                const values = [48.2, 18.5, 12.3, 7.4];
                renderKomposisiChart(labels, values);
            });
    }

    function renderKomposisiChart(labels, values) {
        new Chart(komposisiCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#059669', // Emerald
                        '#F59E0B', // Amber
                        '#0284C7', // Sky
                        '#8B5CF6'  // Purple
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 8,
                            font: { size: 10 },
                            color: '#475569'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ` ${context.label}: ${context.raw} kg`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
