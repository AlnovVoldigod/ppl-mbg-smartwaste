@props([
    'title',
    'value',
    'unit' => null,
    'icon' => null,
    'trend' => null,
    'color' => 'emerald',
])

@php
    // Palet warna aksen untuk latar ikon dan border
    $colorMap = [
        'emerald' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-100',
            'trend_positive' => 'text-emerald-700',
        ],
        'amber' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-700',
            'border' => 'border-amber-100',
            'trend_positive' => 'text-emerald-700',
        ],
        'red' => [
            'bg' => 'bg-rose-50',
            'text' => 'text-rose-700',
            'border' => 'border-rose-100',
            'trend_positive' => 'text-emerald-700',
        ],
        'blue' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'border' => 'border-blue-100',
            'trend_positive' => 'text-emerald-700',
        ],
        'indigo' => [
            'bg' => 'bg-indigo-50',
            'text' => 'text-indigo-700',
            'border' => 'border-indigo-100',
            'trend_positive' => 'text-emerald-700',
        ],
        'slate' => [
            'bg' => 'bg-slate-100',
            'text' => 'text-slate-700',
            'border' => 'border-slate-200',
            'trend_positive' => 'text-emerald-700',
        ],
    ];

    $palette = $colorMap[$color] ?? $colorMap['emerald'];

    // Evaluasi nilai tren
    $trendPositive = null;
    $trendFormatted = null;

    if (!is_null($trend)) {
        $trendString = trim((string) $trend);
        if (str_starts_with($trendString, '+')) {
            $trendPositive = true;
            $trendFormatted = $trendString;
        } elseif (str_starts_with($trendString, '-')) {
            $trendPositive = false;
            $trendFormatted = $trendString;
        } else {
            $numVal = floatval($trendString);
            $trendPositive = $numVal >= 0;
            $trendFormatted = ($numVal > 0 ? '+' : '') . $numVal . '%';
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-xs border border-slate-200 p-5 hover:shadow-md transition-all duration-200 relative overflow-hidden flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 truncate block">
                    {{ $title }}
                </span>
                <div class="mt-2 flex items-baseline gap-1.5 flex-wrap">
                    <span class="text-3xl font-extrabold text-slate-900 tabular-nums tracking-tight">
                        {{ $value }}
                    </span>
                    @if($unit)
                        <span class="text-sm font-semibold text-slate-500">
                            {{ $unit }}
                        </span>
                    @endif
                </div>
            </div>

            @if($icon)
                <div class="w-12 h-12 rounded-xl {{ $palette['bg'] }} {{ $palette['text'] }} {{ $palette['border'] }} border flex items-center justify-center shrink-0 shadow-2xs">
                    {!! $icon !!}
                </div>
            @elseif(isset($slot) && $slot->isNotEmpty())
                <div class="w-12 h-12 rounded-xl {{ $palette['bg'] }} {{ $palette['text'] }} {{ $palette['border'] }} border flex items-center justify-center shrink-0 shadow-2xs">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>

    @if(!is_null($trendFormatted))
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            <div class="inline-flex items-center gap-1 font-semibold {{ $trendPositive ? 'text-emerald-600' : 'text-rose-600' }}">
                @if($trendPositive)
                    <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                    </svg>
                @else
                    <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" />
                    </svg>
                @endif
                <span class="tabular-nums">{{ $trendFormatted }}</span>
            </div>
            <span class="text-slate-400 font-medium">vs periode sebelumnya</span>
        </div>
    @endif
</div>
