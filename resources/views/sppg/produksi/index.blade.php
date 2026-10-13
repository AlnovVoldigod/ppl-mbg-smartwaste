@extends('layouts.app')

@section('title', 'Daftar Produksi Harian - SPPG')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Modul SPPG
                </span>
                <span class="text-xs text-slate-500">• Standar Higiene & Suhu MBG</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Daftar Produksi Harian</h1>
            <p class="text-sm text-slate-600">Catatan pemenuhan kuota memasak, kontrol suhu makanan, dan kesiapan distribusi harian.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sppg.produksi.create') }}" class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Produksi Baru
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Total Batch Hari Ini</div>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">
                {{ $stats['totalBatch'] ?? 4 }} <span class="text-xs font-normal text-slate-500">batch</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Target Porsi Direncanakan</div>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">
                {{ number_format($stats['targetPorsi'] ?? 2400) }} <span class="text-xs font-normal text-slate-500">porsi</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Realisasi Porsi Terpenuhi</div>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-1">
                {{ number_format($stats['realisasiPorsi'] ?? 2415) }} <span class="text-xs font-normal text-slate-500">porsi</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Tingkat Lolos Suhu Aman (&ge;65°C)</div>
            <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">
                {{ $stats['suhuAmanPercent'] ?? '100%' }}
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm" x-data="{ openFilter: false }">
        <form method="GET" action="{{ route('sppg.produksi.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date', now()->subDays(7)->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-700 mb-1">Status Suhu</label>
                <select name="status_suhu" class="w-full rounded-lg border-slate-300 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Kondisi</option>
                    <option value="layak" {{ request('status_suhu') == 'layak' ? 'selected' : '' }}>Suhu Layak Kirim (Aman)</option>
                    <option value="tidak_layak" {{ request('status_suhu') == 'tidak_layak' ? 'selected' : '' }}>Perlu Tindakan Khusus</option>
                </select>
            </div>
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('sppg.produksi.index') }}" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Container wrapped in x-data-table -->
    <x-data-table>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Histori Batch Produksi Dapur</h2>
                    <p class="text-xs text-slate-500">Data kuota porsi terverifikasi dan waktu kesiapan distribusi</p>
                </div>
                <span class="text-xs text-slate-500 font-mono">
                    Total: {{ isset($produksis) && method_exists($produksis, 'total') ? $produksis->total() : 6 }} Data
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Menu Makanan</th>
                            <th class="px-5 py-3.5 text-right">Target Porsi</th>
                            <th class="px-5 py-3.5 text-right">Realisasi</th>
                            <th class="px-5 py-3.5 text-center">Jadwal Selesai</th>
                            <th class="px-5 py-3.5 text-center">Status Suhu</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($produksis ?? [
                            (object)[
                                'id' => 1,
                                'tanggal' => now()->format('Y-m-d'),
                                'menu' => (object)['nama' => 'Ayam Woku Balanga, Sayur Bening Bayam, Pisang Barangan'],
                                'target_porsi' => 850,
                                'realisasi_porsi' => 850,
                                'jam_selesai_masak' => '08:30',
                                'jam_selesai_kemas' => '09:15',
                                'suhu_layak' => true,
                                'suhu_aktual' => 68.5,
                            ],
                            (object)[
                                'id' => 2,
                                'tanggal' => now()->format('Y-m-d'),
                                'menu' => (object)['nama' => 'Semur Daging Telur Puyuh, Tumis Buncis Jagung, Apel'],
                                'target_porsi' => 650,
                                'realisasi_porsi' => 660,
                                'jam_selesai_masak' => '08:45',
                                'jam_selesai_kemas' => '09:30',
                                'suhu_layak' => true,
                                'suhu_aktual' => 70.2,
                            ],
                            (object)[
                                'id' => 3,
                                'tanggal' => now()->format('Y-m-d'),
                                'menu' => (object)['nama' => 'Ikan Bakar Bumbu Kuning, Sayur Lodeh, Jeruk'],
                                'target_porsi' => 900,
                                'realisasi_porsi' => 905,
                                'jam_selesai_masak' => '09:00',
                                'jam_selesai_kemas' => '09:40',
                                'suhu_layak' => true,
                                'suhu_aktual' => 66.8,
                            ],
                            (object)[
                                'id' => 4,
                                'tanggal' => now()->subDay()->format('Y-m-d'),
                                'menu' => (object)['nama' => 'Nasi Liwet Sunda, Ayam Goreng Lengkuas, Lalap Tempe'],
                                'target_porsi' => 800,
                                'realisasi_porsi' => 800,
                                'jam_selesai_masak' => '08:20',
                                'jam_selesai_kemas' => '09:05',
                                'suhu_layak' => true,
                                'suhu_aktual' => 69.0,
                            ],
                            (object)[
                                'id' => 5,
                                'tanggal' => now()->subDays(2)->format('Y-m-d'),
                                'menu' => (object)['nama' => 'Rolade Sapi Saus Tiram, Capcay Bakso, Melon Segar'],
                                'target_porsi' => 850,
                                'realisasi_porsi' => 840,
                                'jam_selesai_masak' => '08:50',
                                'jam_selesai_kemas' => '09:35',
                                'suhu_layak' => false,
                                'suhu_aktual' => 59.2,
                            ]
                        ] as $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-900 font-semibold max-w-sm truncate" title="{{ $item->menu->nama ?? '-' }}">
                                    {{ $item->menu->nama ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums text-slate-600">
                                    {{ number_format($item->target_porsi) }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold {{ $item->realisasi_porsi >= $item->target_porsi ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ number_format($item->realisasi_porsi) }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono text-slate-700">
                                    <span class="text-slate-500 text-[11px]">Kemas:</span> {{ $item->jam_selesai_kemas ?? '-' }} WIB
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($item->suhu_layak ?? true)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Layak ({{ $item->suhu_aktual ?? 68 }}°C)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Perhatian ({{ $item->suhu_aktual ?? 59 }}°C)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('sppg.produksi.edit', $item->id) }}" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded transition-colors" title="Ubah Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('sppg.produksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data produksi ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="Hapus Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada data produksi yang tercatat pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($produksis) && method_exists($produksis, 'hasPages') && $produksis->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50">
                    {{ $produksis->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-data-table>
</div>
@endsection
