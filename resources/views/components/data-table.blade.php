@props([
    'headers' => [],
    'searchable' => true,
    'id' => 'data-table',
    'title' => null,
    'description' => null,
])

@php
    // Normalisasi struktur header jika ada variasi format (string vs array)
    $normalizedHeaders = [];
    foreach ($headers as $index => $header) {
        if (is_array($header)) {
            $normalizedHeaders[] = [
                'index' => $index,
                'label' => $header['label'] ?? '',
                'sortable' => $header['sortable'] ?? true,
                'align' => $header['align'] ?? 'left',
                'class' => $header['class'] ?? '',
            ];
        } else {
            $normalizedHeaders[] = [
                'index' => $index,
                'label' => (string) $header,
                'sortable' => true,
                'align' => 'left',
                'class' => '',
            ];
        }
    }
@endphp

<div 
    id="{{ $id }}"
    x-data="{
        searchQuery: '',
        sortCol: null,
        sortAsc: true,
        noResults: false,
        sortBy(index) {
            if (this.sortCol === index) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortCol = index;
                this.sortAsc = true;
            }
            this.executeSort(index);
        },
        executeSort(index) {
            const tbody = this.$refs.tableBody;
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr:not([data-empty])'));
            rows.sort((rowA, rowB) => {
                const cellA = rowA.children[index]?.innerText.trim() || '';
                const cellB = rowB.children[index]?.innerText.trim() || '';
                
                // Cek apakah berupa angka / persentase / mata uang
                const cleanA = cellA.replace(/[^0-9.-]+/g, '');
                const cleanB = cellB.replace(/[^0-9.-]+/g, '');
                const numA = parseFloat(cleanA);
                const numB = parseFloat(cleanB);

                if (!isNaN(numA) && !isNaN(numB) && cleanA !== '' && cleanB !== '') {
                    return this.sortAsc ? numA - numB : numB - numA;
                }

                return this.sortAsc 
                    ? cellA.localeCompare(cellB, 'id', { numeric: true, sensitivity: 'base' })
                    : cellB.localeCompare(cellA, 'id', { numeric: true, sensitivity: 'base' });
            });
            rows.forEach(r => tbody.appendChild(r));
        },
        filterTable() {
            const tbody = this.$refs.tableBody;
            if (!tbody) return;
            const q = this.searchQuery.toLowerCase().trim();
            const rows = tbody.querySelectorAll('tr:not([data-empty])');
            let visibleCount = 0;
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const match = text.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            this.noResults = (visibleCount === 0 && rows.length > 0);
        }
    }"
    {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden']) }}
>
    {{-- Header Toolbar --}}
    @if($title || $searchable || isset($actions))
        <div class="p-5 border-b border-slate-200/80 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if($description)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
                @endif
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                @if(isset($actions))
                    {{ $actions }}
                @endif

                @if($searchable)
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            @input="filterTable()" 
                            placeholder="Cari dalam tabel..." 
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50/50 hover:bg-white transition-colors"
                        />
                        <button 
                            type="button" 
                            x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''; filterTable()" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tabel Utama --}}
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-slate-50/90 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    @foreach($normalizedHeaders as $h)
                        <th 
                            scope="col" 
                            class="px-5 py-3.5 {{ $h['align'] === 'right' ? 'text-right' : ($h['align'] === 'center' ? 'text-center' : 'text-left') }} {{ $h['class'] }} select-none {{ $h['sortable'] ? 'cursor-pointer hover:bg-slate-100/80 transition-colors' : '' }}"
                            @if($h['sortable'])
                                @click="sortBy({{ $h['index'] }})"
                            @endif
                        >
                            <div class="inline-flex items-center gap-1.5 {{ $h['align'] === 'right' ? 'flex-row-reverse' : '' }}">
                                <span>{{ $h['label'] }}</span>
                                @if($h['sortable'])
                                    <span class="inline-flex flex-col text-slate-400 text-[10px]">
                                        <svg 
                                            class="w-3 h-3 transition-colors" 
                                            :class="sortCol === {{ $h['index'] }} ? (sortAsc ? 'text-emerald-600' : 'text-slate-300') : 'text-slate-300'" 
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                        <svg 
                                            class="w-3 h-3 -mt-1 transition-colors" 
                                            :class="sortCol === {{ $h['index'] }} ? (!sortAsc ? 'text-emerald-600' : 'text-slate-300') : 'text-slate-300'" 
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody 
                x-ref="tableBody" 
                class="divide-y divide-slate-100 bg-white text-slate-700 [&>tr:nth-child(even)]:bg-slate-50/40 [&>tr:hover]:bg-emerald-50/30 [&>tr]:transition-colors"
            >
                {{ $slot }}

                {{-- Status Tidak Ditemukan Saat Pencarian --}}
                <tr x-show="noResults" style="display: none;" data-empty="true">
                    <td colspan="{{ count($normalizedHeaders) ?: 10 }}" class="px-6 py-10 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span class="text-sm font-semibold text-slate-700">Tidak ada data yang sesuai</span>
                            <span class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian yang lain</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Slot Paginasi / Footer --}}
    @if(isset($pagination))
        <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50/70">
            {{ $pagination }}
        </div>
    @endif
</div>
