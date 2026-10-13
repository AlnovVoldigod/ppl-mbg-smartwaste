<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Produksi;
use App\Models\Sppg;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduksiController extends Controller
{
    /**
     * Default SPPG ID untuk mode prototipe tanpa autentikasi ketat.
     */
    protected int $defaultSppgId = 1;

    /**
     * Menampilkan daftar produksi pangan untuk SPPG.
     */
    public function index(Request $request): View
    {
        $sppgId = $request->query('sppg_id', $this->defaultSppgId);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Produksi::with(['menu', 'sppg', 'distribusi.sekolah'])
            ->where('sppg_id', $sppgId);

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $produksiList = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $sppg = Sppg::find($sppgId) ?? Sppg::first();

        // Ringkasan metrik produksi
        $totalPorsiBulanIni = Produksi::where('sppg_id', $sppgId)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('target_porsi');

        $totalBatchProduksi = Produksi::where('sppg_id', $sppgId)->count();

        return view('sppg.produksi.index', compact(
            'produksiList',
            'sppg',
            'startDate',
            'endDate',
            'totalPorsiBulanIni',
            'totalBatchProduksi'
        ));
    }

    /**
     * Menampilkan formulir pencatatan rencana produksi baru.
     */
    public function create(Request $request): View
    {
        $sppgId = $request->query('sppg_id', $this->defaultSppgId);
        $sppg = Sppg::find($sppgId) ?? Sppg::first();
        $menus = Menu::where('status_aktif', true)->orderBy('nama_menu')->get();

        return view('sppg.produksi.create', compact('sppg', 'menus'));
    }

    /**
     * Menyimpan data produksi baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sppg_id' => 'nullable|integer',
            'menu_id' => 'required|integer',
            'tanggal' => 'required|date',
            'target_porsi' => 'required|integer|min:1',
            'jam_mulai_masak' => 'required',
            'jam_selesai_masak' => 'required',
            'jam_selesai_kemas' => 'required',
            'suhu_layak' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'menu_id.required' => 'Menu MBG wajib dipilih.',
            'tanggal.required' => 'Tanggal produksi wajib diisi.',
            'target_porsi.required' => 'Target porsi harus diisi dan lebih dari 0.',
            'jam_mulai_masak.required' => 'Jam mulai masak wajib diisi.',
            'jam_selesai_masak.required' => 'Jam selesai masak wajib diisi.',
            'jam_selesai_kemas.required' => 'Jam selesai kemas wajib diisi.',
            'suhu_layak.required' => 'Suhu kelayakan makanan wajib dicatat.',
        ]);

        if (empty($validated['sppg_id'])) {
            $validated['sppg_id'] = $this->defaultSppgId;
        }

        $produksi = Produksi::create($validated);

        return redirect()->route('sppg.produksi.index')
            ->with('success', 'Rencana produksi MBG berhasil disimpan dengan kode #' . $produksi->id . '.');
    }

    /**
     * Menampilkan detail satu batch produksi beserta data distribusinya.
     */
    public function show(int $id): View
    {
        $produksi = Produksi::with(['menu', 'sppg', 'distribusi.sekolah', 'distribusi.penerimaan.sisaPangan'])
            ->findOrFail($id);

        $totalTerdistribusi = $produksi->distribusi->sum('jumlah_porsi');
        $selisihPorsi = $produksi->target_porsi - $totalTerdistribusi;

        return view('sppg.produksi.show', compact('produksi', 'totalTerdistribusi', 'selisihPorsi'));
    }

    /**
     * Menampilkan form edit data produksi.
     */
    public function edit(int $id): View
    {
        $produksi = Produksi::findOrFail($id);
        $menus = Menu::where('status_aktif', true)->orderBy('nama_menu')->get();
        $sppg = Sppg::find($produksi->sppg_id) ?? Sppg::find($this->defaultSppgId);

        return view('sppg.produksi.edit', compact('produksi', 'menus', 'sppg'));
    }

    /**
     * Memperbarui data produksi yang tersimpan.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $produksi = Produksi::findOrFail($id);

        $validated = $request->validate([
            'menu_id' => 'required|integer',
            'tanggal' => 'required|date',
            'target_porsi' => 'required|integer|min:1',
            'jam_mulai_masak' => 'required',
            'jam_selesai_masak' => 'required',
            'jam_selesai_kemas' => 'required',
            'suhu_layak' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'menu_id.required' => 'Menu MBG wajib dipilih.',
            'tanggal.required' => 'Tanggal produksi wajib diisi.',
            'target_porsi.required' => 'Target porsi harus diisi dan lebih dari 0.',
            'jam_mulai_masak.required' => 'Jam mulai masak wajib diisi.',
            'jam_selesai_masak.required' => 'Jam selesai masak wajib diisi.',
            'jam_selesai_kemas.required' => 'Jam selesai kemas wajib diisi.',
            'suhu_layak.required' => 'Suhu kelayakan makanan wajib dicatat.',
        ]);

        $produksi->update($validated);

        return redirect()->route('sppg.produksi.index')
            ->with('success', 'Data produksi batch #' . $produksi->id . ' berhasil diperbarui.');
    }

    /**
     * Menghapus catatan produksi.
     */
    public function destroy(int $id): RedirectResponse
    {
        $produksi = Produksi::findOrFail($id);

        // Cek jika sudah ada data distribusi terkait
        if ($produksi->distribusi()->exists()) {
            return redirect()->back()
                ->with('error', 'Produksi ini tidak dapat dihapus karena sudah memiliki catatan distribusi terkait.');
        }

        $produksi->delete();

        return redirect()->route('sppg.produksi.index')
            ->with('success', 'Data produksi berhasil dihapus dari sistem.');
    }

    /**
     * Riwayat log operasional SPPG (produksi + distribusi) dalam bentuk timeline.
     */
    public function riwayat(Request $request): View
    {
        $sppgId = $request->query('sppg_id', $this->defaultSppgId);
        $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $riwayatProduksi = Produksi::with(['menu', 'distribusi.sekolah'])
            ->where('sppg_id', $sppgId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai_masak')
            ->get();

        $sppg = Sppg::find($sppgId) ?? Sppg::first();

        return view('sppg.riwayat', compact('riwayatProduksi', 'sppg', 'startDate', 'endDate'));
    }
}
