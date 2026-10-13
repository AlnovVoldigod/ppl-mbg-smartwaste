@props([
    'label',
    'value',
    'unit' => null,
    'color' => 'slate',
])

@php
    $borderColors = [
        'emerald' => 'border-l-emerald-600 bg-emerald-50/15',
        'amber' => 'border-l-amber-500 bg-amber-50/15',
        'red' => 'border-l-rose-600 bg-rose-50/15',
        'rose' => 'border-l-rose-600 bg-rose-50/15',
        'blue' => 'border-l-blue-600 bg-blue-50/15',
        'indigo' => 'border-l-indigo-600 bg-indigo-50/15',
        'slate' => 'border-l-slate-400 bg-slate-50/40',
    ];

    $accentClass = $borderColors[$color] ?? $borderColors['slate'];
@endphp

<div {{ $attributes->merge(['class' => "p-3.5 rounded-lg border border-slate-200 bg-white border-l-4 shadow-2xs {$accentClass}"]) }}>
    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider truncate mb-1">
        {{ $label }}
    </p>
    <div class="flex items-baseline gap-1">
        <span class="text-xl font-bold text-slate-900 tabular-nums tracking-tight">
            {{ $value }}
        </span>
        @if($unit)
            <span class="text-xs font-medium text-slate-500">
                {{ $unit }}
            </span>
        @endif
    </div>
</div>
