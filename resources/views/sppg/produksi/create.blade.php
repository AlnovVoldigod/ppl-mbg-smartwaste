@extends('layouts.app')

@section('title', 'Input Produksi Harian - SPPG')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('sppg.produksi.index') }}" class="hover:text-emerald-700 transition-colors">Produksi Harian</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Input Baru</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Input Data Produksi Dapur</h1>
            <p class="text-sm text-slate-600">Catat parameter operasional memasak harian, kepatuhan porsi, dan pemantauan suhu pengiriman.</p>
        </div>
        <a href="{{ route('sppg.produksi.index') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Container -->
    <x-form-section>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8" x-data="{
            suhuLayak: true,
            suhuNilai: 68.0,
            targetPorsi: 0,
            realisasiPorsi: 0,
            get selisih() {
                return (this.realisasiPorsi || 0) - (this.targetPorsi || 0);
            }
        }">
            <form action="{{ route('sppg.produksi.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Section 1: Informasi Menu & Tanggal -->
                <div class="border-b border-slate-100 pb-6">
                    <h2 class="text-sm font-bold text-slate-900 mb-1">1. Menu & Alokasi Porsi</h2>
                    <p class="text-xs text-slate-500 mb-4">Pilih resep terdaftar dan tetapkan target kuota makan bergizi.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="menu_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Menu Makanan Terdaftar <span class="text-rose-500">*</span>
                            </label>
                            <select id="menu_id" name="menu_id" required class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="">-- Pilih Menu Terjadwal Hari Ini --</option>
                                @if(isset($menus) && count($menus) > 0)
                                    @foreach($menus as $menu)
                                        <option value="{{ $menu->id }}" {{ old('menu_id') == $menu->id ? 'selected' : '' }}>
                                            {{ $menu->nama }} (Target Kalori: {{ $menu->kalori ?? '550' }} kkal)
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1">Ayam Suwir Balado Manis + Sayur Sop Jamur + Pisang Barangan (560 kkal)</option>
                                    <option value="2">Rolade Daging Sapi + Capcay Brokoli Wortel + Jeruk Manis (580 kkal)</option>
                                    <option value="3">Ikan Kembung Bakar Kecap + Cah Kangkung Tempe + Semangka (520 kkal)</option>
                                    <option value="4">Semur Telur & Tahu Sutra + Sayur Lodeh Labu Siam + Apel (510 kkal)</option>
                                @endif
                            </select>
                            @error('menu_id')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Produksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            @error('tanggal')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="target_porsi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Target Porsi <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" id="target_porsi" name="target_porsi" x-model.number="targetPorsi" min="1" placeholder="800" value="{{ old('target_porsi', 800) }}" required class="w-full rounded-lg border-slate-300 text-sm font-mono text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                @error('target_porsi')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="realisasi_porsi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Realisasi Porsi <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" id="realisasi_porsi" name="realisasi_porsi" x-model.number="realisasiPorsi" min="0" placeholder="800" value="{{ old('realisasi_porsi', 800) }}" required class="w-full rounded-lg border-slate-300 text-sm font-mono text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                @error('realisasi_porsi')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Jadwal & Waktu Pemrosesan -->
                <div class="border-b border-slate-100 pb-6">
                    <h2 class="text-sm font-bold text-slate-900 mb-1">2. Catatan Waktu Pemrosesan</h2>
                    <p class="text-xs text-slate-500 mb-4">Waktu higienis memasak hingga pengemasan kedap udara.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="jam_mulai_masak" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Mulai Masak <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="jam_mulai_masak" name="jam_mulai_masak" value="{{ old('jam_mulai_masak', '06:30') }}" required class="w-full rounded-lg border-slate-300 text-sm font-mono text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            @error('jam_mulai_masak')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_selesai_masak" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Selesai Masak <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="jam_selesai_masak" name="jam_selesai_masak" value="{{ old('jam_selesai_masak', '08:45') }}" required class="w-full rounded-lg border-slate-300 text-sm font-mono text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            @error('jam_selesai_masak')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_selesai_kemas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Selesai Kemas <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="jam_selesai_kemas" name="jam_selesai_kemas" value="{{ old('jam_selesai_kemas', '09:30') }}" required class="w-full rounded-lg border-slate-300 text-sm font-mono text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            @error('jam_selesai_kemas')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Standar Kontrol Suhu Layak Kirim -->
                <div class="border-b border-slate-100 pb-6">
                    <h2 class="text-sm font-bold text-slate-900 mb-1">3. Uji Kelaikan & Suhu Makanan</h2>
                    <p class="text-xs text-slate-500 mb-4">Sesuai SOP BGN, makanan hangat wajib didistribusikan pada suhu minimum 65°C.</p>

                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <!-- Alpine Toggle -->
                                <button type="button" 
                                        @click="suhuLayak = !suhuLayak" 
                                        :class="suhuLayak ? 'bg-emerald-600' : 'bg-slate-300'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    <span :class="suhuLayak ? 'translate-x-5' : 'translate-x-0'"
                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                </button>
                                <div>
                                    <input type="hidden" name="suhu_layak" :value="suhuLayak ? 1 : 0">
                                    <span class="text-sm font-bold text-slate-900">Suhu Layak Kirim Terpenuhi</span>
                                    <p class="text-xs text-slate-500">Makanan telah dicek menggunakan termometer probe pangan digital</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <label for="suhu_aktual" class="text-xs font-semibold text-slate-700 whitespace-nowrap">Suhu Terukur (°C):</label>
                                <input type="number" step="0.1" id="suhu_aktual" name="suhu_aktual" x-model="suhuNilai" class="w-24 rounded-lg border-slate-300 text-sm font-mono text-slate-800 text-right focus:border-emerald-500 focus:ring-emerald-500">
                                <span class="text-xs text-slate-600 font-bold">°C</span>
                            </div>
                        </div>

                        <!-- Real-time warning alert if temperature below safe threshold -->
                        <div x-show="!suhuLayak || suhuNilai < 65" x-cloak class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-2.5 text-xs text-amber-900">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <strong>Peringatan Suhu:</strong> Suhu di bawah 65°C berisiko terhadap pertumbuhan bakteri jika waktu tempuh distribusi melebihi 45 menit. Pastikan thermal box terinsulasi sempurna.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Catatan Operasional -->
                <div>
                    <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan Operasional / Keterangan Khusus
                    </label>
                    <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: Pengemasan menggunakan thermal box steril nomor 01-12. Sampel makanan telah disimpan di freezer bank sampel." class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('sppg.produksi.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Simpan Data Produksi
                    </button>
                </div>
            </form>
        </div>
    </x-form-section>
</div>
@endsection
