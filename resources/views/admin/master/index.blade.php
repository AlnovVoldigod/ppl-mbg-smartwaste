@extends('layouts.app')

@php
    $type = request('type', $type ?? 'sppg');
@endphp

@section('title', 'Master Data ' . strtoupper($type) . ' - Admin MBG')

@section('content')
<div class="space-y-6" x-data="{
    currentType: '{{ $type }}',
    modalOpen: false,
    modalMode: 'create', // create | edit
    formData: {},
    openCreate() {
        this.modalMode = 'create';
        this.formData = {};
        this.modalOpen = true;
    },
    openEdit(item) {
        this.modalMode = 'edit';
        this.formData = { ...item };
        this.modalOpen = true;
    },
    closeModal() {
        this.modalOpen = false;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Konfigurasi Sistem
                </span>
                <span class="text-xs text-slate-500">• Manajemen Master Data</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Master Data {{ ucfirst($type) }}</h1>
            <p class="text-sm text-slate-600">Kelola entitas SPPG produsen, daftar sekolah penerima sasaran, dan master menu makanan bergizi.</p>
        </div>

        <div>
            <button type="button" @click="openCreate()" class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Data {{ ucfirst($type) }}
            </button>
        </div>
    </div>

    <!-- Navigation Pills between Master Types -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('admin.master.index', ['type' => 'sppg']) }}" 
           class="px-4 py-2 text-xs font-bold rounded-lg transition-colors {{ $type === 'sppg' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            🏢 SPPG Produsen
        </a>
        <a href="{{ route('admin.master.index', ['type' => 'sekolah']) }}" 
           class="px-4 py-2 text-xs font-bold rounded-lg transition-colors {{ $type === 'sekolah' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            🏫 Sekolah Penerima
        </a>
        <a href="{{ route('admin.master.index', ['type' => 'menu']) }}" 
           class="px-4 py-2 text-xs font-bold rounded-lg transition-colors {{ $type === 'menu' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            🍱 Resep &amp; Menu Makan
        </a>
    </div>

    <!-- Master Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Daftar Terdaftar (Kategori: {{ strtoupper($type) }})</h2>
            <span class="text-xs text-slate-500 font-mono">Aktif &amp; Terintegrasi</span>
        </div>

        <div class="overflow-x-auto">
            @if($type === 'sppg')
                <!-- SPPG Table -->
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Kode SPPG</th>
                            <th class="px-5 py-3.5">Nama Dapur SPPG</th>
                            <th class="px-5 py-3.5">Penanggung Jawab</th>
                            <th class="px-5 py-3.5">Wilayah / Alamat</th>
                            <th class="px-5 py-3.5 text-right">Kapasitas Harian</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($items ?? [
                            (object)['id' => 1, 'kode' => 'SPPG-01', 'nama' => 'SPPG Sentra Sejahtera 01', 'pj' => 'Dra. Endang Sulastri', 'wilayah' => 'Jakarta Pusat - Menteng', 'kapasitas' => 2500, 'status' => 'Aktif'],
                            (object)['id' => 2, 'kode' => 'SPPG-02', 'nama' => 'SPPG Bina Gizi Mandiri', 'pj' => 'Ir. Bambang Trihatmojo', 'wilayah' => 'Jakarta Pusat - Senen', 'kapasitas' => 2200, 'status' => 'Aktif'],
                            (object)['id' => 3, 'kode' => 'SPPG-03', 'nama' => 'SPPG Dapur Utama Patriot', 'pj' => 'Hj. Siti Aminah', 'wilayah' => 'Jakarta Timur - Matraman', 'kapasitas' => 3000, 'status' => 'Aktif'],
                        ] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $row->kode }}</td>
                                <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $row->nama }}</td>
                                <td class="px-5 py-3.5 text-slate-700">{{ $row->pj }}</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $row->wilayah }}</td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums text-emerald-700 font-bold">{{ number_format($row->kapasitas) }} porsi</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $row->status }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <button type="button" @click="openEdit({{ json_encode($row) }})" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded transition-colors mr-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data SPPG.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($type === 'sekolah')
                <!-- Sekolah Table -->
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">NPSN</th>
                            <th class="px-5 py-3.5">Nama Sekolah</th>
                            <th class="px-5 py-3.5">Kepala Sekolah / PIC</th>
                            <th class="px-5 py-3.5 text-center">Jenjang</th>
                            <th class="px-5 py-3.5 text-right">Siswa Sasaran</th>
                            <th class="px-5 py-3.5">SPPG Pembina</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($items ?? [
                            (object)['id' => 1, 'npsn' => '20100123', 'nama' => 'SDN 01 Menteng Jaya', 'kepsek' => 'Drs. Subroto, M.Pd', 'jenjang' => 'SD', 'siswa' => 450, 'sppg' => 'SPPG Sentra Sejahtera 01'],
                            (object)['id' => 2, 'npsn' => '20100456', 'nama' => 'SDN 04 Kebon Sirih', 'kepsek' => 'Hj. Nurlaila, S.Pd', 'jenjang' => 'SD', 'siswa' => 400, 'sppg' => 'SPPG Sentra Sejahtera 01'],
                            (object)['id' => 3, 'npsn' => '20100789', 'nama' => 'SMPN 15 Harmoni', 'kepsek' => 'Budi Santoso, M.Si', 'jenjang' => 'SMP', 'siswa' => 660, 'sppg' => 'SPPG Bina Gizi Mandiri'],
                            (object)['id' => 4, 'npsn' => '20100999', 'nama' => 'SMPN 02 Salemba', 'kepsek' => 'Ratnawati, M.Pd', 'jenjang' => 'SMP', 'siswa' => 700, 'sppg' => 'SPPG Dapur Utama Patriot'],
                        ] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $row->npsn }}</td>
                                <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $row->nama }}</td>
                                <td class="px-5 py-3.5 text-slate-700">{{ $row->kepsek }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $row->jenjang }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums text-emerald-700 font-bold">{{ number_format($row->siswa) }} siswa</td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $row->sppg }}</td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <button type="button" @click="openEdit({{ json_encode($row) }})" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded transition-colors mr-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data sekolah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <!-- Menu Table -->
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Kode Menu</th>
                            <th class="px-5 py-3.5">Nama Menu Masakan</th>
                            <th class="px-5 py-3.5">Komposisi Utama</th>
                            <th class="px-5 py-3.5 text-right">Kandungan Kalori</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($items ?? [
                            (object)['id' => 1, 'kode' => 'MN-001', 'nama' => 'Ayam Woku Balanga + Sop Bayam + Pisang', 'komposisi' => 'Ayam, Jagung, Bayam, Wortel, Pisang Barangan', 'kalori' => 560, 'status' => 'Aktif'],
                            (object)['id' => 2, 'kode' => 'MN-002', 'nama' => 'Rolade Sapi + Capcay Brokoli + Jeruk', 'komposisi' => 'Daging Sapi, Telur, Brokoli, Wortel, Jeruk Medan', 'kalori' => 580, 'status' => 'Aktif'],
                            (object)['id' => 3, 'kode' => 'MN-003', 'nama' => 'Ikan Bakar Kuning + Sayur Lodeh + Semangka', 'komposisi' => 'Ikan Kembung, Labu Siam, Santan Encer, Semangka', 'kalori' => 520, 'status' => 'Aktif'],
                            (object)['id' => 4, 'kode' => 'MN-004', 'nama' => 'Semur Telur & Tahu + Tumis Buncis + Apel', 'komposisi' => 'Telur Puyuh, Tahu Putih, Buncis, Jagung Manis, Apel Fuji', 'kalori' => 510, 'status' => 'Aktif'],
                        ] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $row->kode }}</td>
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $row->nama }}</td>
                                <td class="px-5 py-3.5 text-slate-600 max-w-xs truncate">{{ $row->komposisi }}</td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums text-slate-900 font-bold">{{ $row->kalori }} kkal</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $row->status }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <button type="button" @click="openEdit({{ json_encode($row) }})" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded transition-colors mr-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada data menu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Modal Create / Edit Form -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all" @click.away="closeModal()">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-mono uppercase tracking-wider text-emerald-400" x-text="modalMode === 'create' ? 'Tambah Data Baru' : 'Perbarui Data'"></span>
                    <h2 class="text-lg font-bold text-white mt-0.5">Form Master Data {{ ucfirst($type) }}</h2>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="id" :value="formData.id || ''">

                @if($type === 'sppg')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode SPPG</label>
                        <input type="text" name="kode" x-model="formData.kode" placeholder="SPPG-04" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama SPPG</label>
                        <input type="text" name="nama" x-model="formData.nama" placeholder="SPPG Maju Barokah" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Penanggung Jawab</label>
                        <input type="text" name="pj" x-model="formData.pj" placeholder="Nama Penanggung Jawab" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah / Alamat</label>
                        <input type="text" name="wilayah" x-model="formData.wilayah" placeholder="Kecamatan / Kota" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kapasitas Harian (Porsi)</label>
                        <input type="number" name="kapasitas" x-model="formData.kapasitas" placeholder="2500" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                @elseif($type === 'sekolah')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NPSN</label>
                        <input type="text" name="npsn" x-model="formData.npsn" placeholder="20101111" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Sekolah</label>
                        <input type="text" name="nama" x-model="formData.nama" placeholder="SDN 05 Cempaka Putih" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kepala Sekolah</label>
                        <input type="text" name="kepsek" x-model="formData.kepsek" placeholder="Nama Kepala Sekolah" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenjang</label>
                            <select name="jenjang" x-model="formData.jenjang" class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Siswa Sasaran</label>
                            <input type="number" name="siswa" x-model="formData.siswa" placeholder="450" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Menu</label>
                        <input type="text" name="kode" x-model="formData.kode" placeholder="MN-005" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Menu</label>
                        <input type="text" name="nama" x-model="formData.nama" placeholder="Ayam Panggang Kecap + Cah Buncis" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Komposisi Bahan Utama</label>
                        <textarea name="komposisi" x-model="formData.komposisi" rows="2" placeholder="Komposisi protein, sayuran, dan buah pelengkap" class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kandungan Kalori (kkal)</label>
                        <input type="number" name="kalori" x-model="formData.kalori" placeholder="550" required class="w-full rounded-lg border-slate-300 text-xs text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
