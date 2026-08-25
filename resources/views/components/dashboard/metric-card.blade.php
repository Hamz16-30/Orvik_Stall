@props([
    'label' => 'Total Sales',
    'value' => '$0.00',
    'change' => '+0.0%',
    'trend' => 'up', // up, down, neutral
    'icon' => 'dollar-sign',
    'badge' => null,
])

<div class="bg-white p-4 sm:p-5 rounded-xl border border-border/80 shadow-2xs hover:shadow-xs transition-shadow">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $label }}</span>
        <div class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-foreground/80">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        </div>
    </div>

    <div class="mt-3 flex items-baseline justify-between">
        <h4 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">{{ $value }}</h4>
        @if($change)
            <div class="flex items-center gap-1 text-xs font-semibold {{ $trend === 'up' ? 'text-emerald-600' : ($trend === 'down' ? 'text-rose-600' : 'text-muted-foreground') }}">
                @if($trend === 'up')
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                @elseif($trend === 'down')
                    <i data-lucide="trending-down" class="w-3.5 h-3.5"></i>
                @endif
                <span>{{ $change }}</span>
            </div>
        @endif
    </div>
</div>
