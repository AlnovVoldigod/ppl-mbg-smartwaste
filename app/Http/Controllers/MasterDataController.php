<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Sekolah;
use App\Models\Sppg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    /**
     * Menampilkan daftar data master berdasarkan tipe entitas (sppg, sekolah, menu).
     */
    public function index(Request $request, ?string $type = null): View
    {
        $type = $type ?? $request->route('type') ?? $request->query('type', 'sppg');

        $search = $request->query('search');

        switch ($type) {
            case 'sekolah':
                $query = Sekolah::query();
                if ($search) {
                    $query->where('nama_sekolah', 'like', "%{$search}%")
                        ->orWhere('npsn', 'like', "%{$search}%")
                        ->orWhere('kepala_sekolah', 'like', "%{$search}%");
                }
                $data = $query->orderBy('nama_sekolah')->paginate(10)->withQueryString();
                $title = 'Data Master Sekolah Penerima MBG';
                break;

            case 'menu':
                $query = Menu::query();
                if ($search) {
                    $query->where('nama_menu', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                }
                $data = $query->orderBy('nama_menu')->paginate(10)->withQueryString();
                $title = 'Data Master Menu Makanan Bergizi';
                break;

            case 'sppg':
            default:
                $type = 'sppg';
                $query = Sppg::query();
                if ($search) {
                    $query->where('nama_sppg', 'like', "%{$search}%")
                        ->orWhere('kode_sppg', 'like', "%{$search}%")
                        ->orWhere('penanggung_jawab', 'like', "%{$search}%");
                }
                $data = $query->orderBy('nama_sppg')->paginate(10)->withQueryString();
                $title = 'Data Master Satuan Pelayanan Pemenuhan Gizi (SPPG)';
                break;
        }

        // Hitung total ringkasan untuk tab navigasi
        $totalSppg = Sppg::count();
        $totalSekolah = Sekolah::count();
        $totalMenu = Menu::count();

        return view('admin.master.index', compact(
            'data',
            'type',
            'title',
            'search',
            'totalSppg',
            'totalSekolah',
            'totalMenu'
        ));
    }

    /**
     * Menyimpan data master baru berdasarkan tipenya.
     */
    public function store(Request $request, ?string $type = null): RedirectResponse
    {
        $type = $type ?? $request->route('type') ?? $request->input('type', 'sppg');

        switch ($type) {
            case 'sekolah':
                $validated = $request->validate([
                    'nama_sekolah' => 'required|string|max:150',
                    'npsn' => 'required|string|max:20|unique:sekolah,npsn',
                    'kepala_sekolah' => 'nullable|string|max:100',
                    'telepon' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:100',
                    'alamat' => 'nullable|string|max:255',
                    'jumlah_siswa' => 'required|integer|min:1',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_sekolah.required' => 'Nama sekolah wajib diisi.',
                    'npsn.required' => 'NPSN sekolah wajib diisi.',
                    'npsn.unique' => 'NPSN sudah terdaftar.',
                    'jumlah_siswa.required' => 'Jumlah siswa penerima MBG wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : true;
                Sekolah::create($validated);
                $label = 'Sekolah baru';
                break;

            case 'menu':
                $validated = $request->validate([
                    'nama_menu' => 'required|string|max:150',
                    'kategori' => 'nullable|string|max:50',
                    'deskripsi' => 'nullable|string|max:500',
                    'kalori' => 'nullable|integer|min:0',
                    'berat_standar_porsi' => 'nullable|numeric|min:0',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_menu.required' => 'Nama menu MBG wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : true;
                Menu::create($validated);
                $label = 'Menu makanan baru';
                break;

            case 'sppg':
            default:
                $type = 'sppg';
                $validated = $request->validate([
                    'nama_sppg' => 'required|string|max:150',
                    'kode_sppg' => 'required|string|max:30|unique:sppg,kode_sppg',
                    'penanggung_jawab' => 'nullable|string|max:100',
                    'telepon' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:100',
                    'alamat' => 'nullable|string|max:255',
                    'kapasitas_harian' => 'required|integer|min:1',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_sppg.required' => 'Nama SPPG wajib diisi.',
                    'kode_sppg.required' => 'Kode unik SPPG wajib diisi.',
                    'kode_sppg.unique' => 'Kode SPPG sudah digunakan.',
                    'kapasitas_harian.required' => 'Kapasitas produksi harian wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : true;
                Sppg::create($validated);
                $label = 'SPPG baru';
                break;
        }

        return redirect()->route('admin.master.index', ['type' => $type])
            ->with('success', "Data {$label} berhasil ditambahkan ke sistem.");
    }

    /**
     * Memperbarui data master yang sudah ada.
     */
    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        switch ($type) {
            case 'sekolah':
                $record = Sekolah::findOrFail($id);
                $validated = $request->validate([
                    'nama_sekolah' => 'required|string|max:150',
                    'npsn' => 'required|string|max:20|unique:sekolah,npsn,' . $id,
                    'kepala_sekolah' => 'nullable|string|max:100',
                    'telepon' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:100',
                    'alamat' => 'nullable|string|max:255',
                    'jumlah_siswa' => 'required|integer|min:1',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_sekolah.required' => 'Nama sekolah wajib diisi.',
                    'npsn.required' => 'NPSN sekolah wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : false;
                $record->update($validated);
                $label = 'Sekolah ' . $record->nama_sekolah;
                break;

            case 'menu':
                $record = Menu::findOrFail($id);
                $validated = $request->validate([
                    'nama_menu' => 'required|string|max:150',
                    'kategori' => 'nullable|string|max:50',
                    'deskripsi' => 'nullable|string|max:500',
                    'kalori' => 'nullable|integer|min:0',
                    'berat_standar_porsi' => 'nullable|numeric|min:0',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_menu.required' => 'Nama menu MBG wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : false;
                $record->update($validated);
                $label = 'Menu ' . $record->nama_menu;
                break;

            case 'sppg':
            default:
                $type = 'sppg';
                $record = Sppg::findOrFail($id);
                $validated = $request->validate([
                    'nama_sppg' => 'required|string|max:150',
                    'kode_sppg' => 'required|string|max:30|unique:sppg,kode_sppg,' . $id,
                    'penanggung_jawab' => 'nullable|string|max:100',
                    'telepon' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:100',
                    'alamat' => 'nullable|string|max:255',
                    'kapasitas_harian' => 'required|integer|min:1',
                    'status_aktif' => 'nullable|boolean',
                ], [
                    'nama_sppg.required' => 'Nama SPPG wajib diisi.',
                ]);

                $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : false;
                $record->update($validated);
                $label = 'SPPG ' . $record->nama_sppg;
                break;
        }

        return redirect()->route('admin.master.index', ['type' => $type])
            ->with('success', "Data {$label} berhasil diperbarui.");
    }

    /**
     * Menghapus record data master dengan pengecekan integritas relasi.
     */
    public function destroy(string $type, int $id): RedirectResponse
    {
        switch ($type) {
            case 'sekolah':
                $sekolah = Sekolah::findOrFail($id);
                if ($sekolah->distribusi()->exists() || $sekolah->penerimaan()->exists()) {
                    return redirect()->back()
                        ->with('error', "Sekolah '{$sekolah->nama_sekolah}' tidak dapat dihapus karena telah memiliki riwayat distribusi dan penerimaan makanan.");
                }
                $nama = $sekolah->nama_sekolah;
                $sekolah->delete();
                break;

            case 'menu':
                $menu = Menu::findOrFail($id);
                if ($menu->produksi()->exists()) {
                    return redirect()->back()
                        ->with('error', "Menu '{$menu->nama_menu}' tidak dapat dihapus karena telah tercatat dalam batch produksi MBG.");
                }
                $nama = $menu->nama_menu;
                $menu->delete();
                break;

            case 'sppg':
            default:
                $type = 'sppg';
                $sppg = Sppg::findOrFail($id);
                if ($sppg->produksi()->exists() || $sppg->limbahDapur()->exists()) {
                    return redirect()->back()
                        ->with('error', "SPPG '{$sppg->nama_sppg}' tidak dapat dihapus karena telah memiliki catatan operasional produksi atau limbah dapur.");
                }
                $nama = $sppg->nama_sppg;
                $sppg->delete();
                break;
        }

        return redirect()->route('admin.master.index', ['type' => $type])
            ->with('success', "Data '{$nama}' berhasil dihapus dari sistem.");
    }
}
