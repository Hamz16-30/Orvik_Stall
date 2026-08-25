@props([
    'variant' => 'default', // active, low-stock, out-of-stock, completed, pending, cancelled, archived, accent, secondary
    'size' => 'md',
    'dot' => true,
    'icon' => null,
])

@php
    $variant = strtolower($variant);

    $styles = match($variant) {
        'active', 'completed', 'success' => [
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'dot' => 'bg-emerald-500',
            'icon' => 'check-circle-2',
        ],
        'low-stock', 'low stock', 'warning' => [
            'class' => 'bg-amber-50 text-amber-700 border-amber-200/60',
            'dot' => 'bg-amber-500',
            'icon' => 'alert-triangle',
        ],
        'out-of-stock', 'out of stock', 'danger', 'destructive' => [
            'class' => 'bg-rose-50 text-rose-700 border-rose-200/60',
            'dot' => 'bg-rose-500',
            'icon' => 'alert-circle',
        ],
        'pending' => [
            'class' => 'bg-sky-50 text-sky-700 border-sky-200/60',
            'dot' => 'bg-sky-500',
            'icon' => 'clock',
        ],
        'cancelled', 'archived' => [
            'class' => 'bg-zinc-100 text-zinc-600 border-zinc-200',
            'dot' => 'bg-zinc-400',
            'icon' => 'archive',
        ],
        'accent' => [
            'class' => 'bg-accent/10 text-accent border-accent/20',
            'dot' => 'bg-accent',
            'icon' => 'sparkles',
        ],
        default => [
            'class' => 'bg-secondary text-secondary-foreground border-border',
            'dot' => 'bg-foreground/40',
            'icon' => null,
        ],
    };

    $sizeClasses = match($size) {
        'sm' => 'text-[11px] px-2 py-0.5 gap-1',
        'lg' => 'text-xs px-3 py-1.5 gap-2',
        default => 'text-xs px-2.5 py-1 gap-1.5',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full border {$styles['class']} {$sizeClasses} select-none"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $styles['dot'] }} shrink-0"></span>
    @elseif($icon || $styles['icon'])
        <i data-lucide="{{ $icon ?? $styles['icon'] }}" class="w-3 h-3 shrink-0"></i>
    @endif
    <span>{{ $slot }}</span>
</span>
