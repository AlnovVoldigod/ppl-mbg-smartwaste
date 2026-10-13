@props([
    'name',
    'title' => '',
    'maxWidth' => '2xl',
])

@php
    $maxWidthMap = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        'full' => 'sm:max-w-7xl',
    ];
    $maxWidthClass = $maxWidthMap[$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<div
    x-data="{ show: false }"
    x-show="show"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}' || (typeof $event.detail === 'object' && $event.detail.name === '{{ $name }}')) show = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}' || (typeof $event.detail === 'object' && $event.detail.name === '{{ $name }}')) show = false"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title-{{ $name }}"
>
    {{-- Overlay Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="show = false"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
    ></div>

    {{-- Panel Konten Modal --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full {{ $maxWidthClass }} bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-8 z-10"
        x-on:click.stop
    >
        {{-- Header Modal --}}
        <div class="px-6 py-4.5 border-b border-slate-200/90 flex items-center justify-between bg-slate-50/50">
            <h3 id="modal-title-{{ $name }}" class="text-base font-bold text-slate-900 tracking-tight">
                {{ $title }}
            </h3>
            <button
                type="button"
                x-on:click="show = false"
                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500"
                aria-label="Tutup jendela modal"
            >
                <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Isi Body Modal --}}
        <div class="p-6 text-slate-700 max-h-[calc(85vh-8rem)] overflow-y-auto scrollbar-thin">
            {{ $slot }}
        </div>

        {{-- Footer Modal (Jika disediakan) --}}
        @if(isset($footer))
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
