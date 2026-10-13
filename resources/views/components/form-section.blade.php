@props([
    'title' => '',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-xs mb-6 overflow-hidden']) }}>
    <div class="p-6 border-b border-slate-100 bg-slate-50/40">
        <div class="flex items-start gap-3">
            @if($icon)
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                    {!! $icon !!}
                </div>
            @endif
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">
                    {{ $title }}
                </h3>
                @if($description)
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>

    @if(isset($actions))
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 rounded-b-xl">
            {{ $actions }}
        </div>
    @endif
</div>
