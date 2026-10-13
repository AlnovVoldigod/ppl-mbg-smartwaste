@props([
    'rate',
    'showRate' => true,
    'size' => 'md', // sm, md, lg
])

@php
    // Normalisasi nilai rate menjadi float
    $numericRate = is_numeric($rate) ? floatval($rate) : floatval(str_replace(['%', ','], ['', '.'], (string) $rate));

    if ($numericRate < 5.0) {
        $level = 'safe';
        $label = 'Aman';
        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80 ring-emerald-600/20';
        $dotClass = 'bg-emerald-500';
    } elseif ($numericRate <= 10.0) {
        $level = 'warning';
        $label = 'Perlu Evaluasi';
        $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200/80 ring-amber-600/20';
        $dotClass = 'bg-amber-500';
    } else {
        $level = 'critical';
        $label = 'Kritis';
        $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200/80 ring-rose-600/20';
        $dotClass = 'bg-rose-500';
    }

    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs gap-1',
        'md' => 'px-2.5 py-1 text-xs gap-1.5',
        'lg' => 'px-3 py-1.5 text-sm gap-2',
    ];
    $currentSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-full border shadow-2xs {$badgeClass} {$currentSize}"]) }}>
    @if($level === 'safe')
        {{-- Ikon Aman (Shield Check / Checkmark) --}}
        <svg class="w-3.5 h-3.5 shrink-0 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
        </svg>
    @elseif($level === 'warning')
        {{-- Ikon Perlu Evaluasi (Exclamation Triangle) --}}
        <svg class="w-3.5 h-3.5 shrink-0 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    @else
        {{-- Ikon Kritis (Exclamation Circle) --}}
        <svg class="w-3.5 h-3.5 shrink-0 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
    @endif

    <span>{{ $label }}</span>

    @if($showRate)
        <span class="opacity-80 font-mono tabular-nums">({{ number_format($numericRate, 1, ',', '.') }}%)</span>
    @endif
</span>
