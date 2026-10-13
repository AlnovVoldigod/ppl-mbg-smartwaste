@props([
    'type' => 'info', // info, warning, danger, success
    'message' => null,
    'dismissible' => true,
    'title' => null,
])

@php
    $typeConfig = [
        'success' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'text' => 'text-emerald-800',
            'icon_color' => 'text-emerald-600',
            'close_hover' => 'hover:bg-emerald-100 text-emerald-600',
            'default_title' => 'Berhasil',
        ],
        'info' => [
            'bg' => 'bg-sky-50',
            'border' => 'border-sky-200',
            'text' => 'text-sky-800',
            'icon_color' => 'text-sky-600',
            'close_hover' => 'hover:bg-sky-100 text-sky-600',
            'default_title' => 'Informasi',
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'text' => 'text-amber-800',
            'icon_color' => 'text-amber-600',
            'close_hover' => 'hover:bg-amber-100 text-amber-600',
            'default_title' => 'Peringatan',
        ],
        'danger' => [
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-200',
            'text' => 'text-rose-800',
            'icon_color' => 'text-rose-600',
            'close_hover' => 'hover:bg-rose-100 text-rose-600',
            'default_title' => 'Perhatian',
        ],
    ];

    $config = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition:leave="transition ease-in duration-200" 
    x-transition:leave-start="opacity-100 transform scale-100" 
    x-transition:leave-end="opacity-0 transform scale-95" 
    {{ $attributes->merge(['class' => "w-full rounded-xl border p-4 shadow-2xs {$config['bg']} {$config['border']} {$config['text']} relative flex items-start gap-3.5 transition-all"]) }}
    role="alert"
>
    {{-- Ikon Alert --}}
    <div class="shrink-0 mt-0.5 {{ $config['icon_color'] }}">
        @if($type === 'success')
            <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif($type === 'warning')
            <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        @elseif($type === 'danger')
            <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        @else
            <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
        @endif
    </div>

    {{-- Pesan Alert --}}
    <div class="flex-1 min-w-0 pr-6 text-sm leading-relaxed">
        @if($title)
            <div class="font-bold text-slate-900 mb-0.5">{{ $title }}</div>
        @endif
        @if($message)
            <div>{{ $message }}</div>
        @endif
        @if($slot->isNotEmpty())
            <div>{{ $slot }}</div>
        @endif
    </div>

    {{-- Tombol Tutup Dismissible --}}
    @if($dismissible)
        <button 
            type="button" 
            @click="show = false" 
            class="absolute top-3.5 right-3.5 p-1 rounded-lg transition-colors {{ $config['close_hover'] }} focus:outline-none focus:ring-2 focus:ring-slate-400"
            aria-label="Tutup pemberitahuan"
        >
            <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
