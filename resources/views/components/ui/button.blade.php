@props([
    'variant' => 'primary',
    'size' => 'md',
    'rounded' => 'full',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98]';

    $variantClasses = match($variant) {
        'accent' => 'bg-accent text-accent-foreground hover:bg-accent/90 shadow-sm shadow-accent/20 active:bg-accent/95',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80 border border-border/80 shadow-sm',
        'ghost' => 'bg-transparent text-foreground hover:bg-muted text-muted-foreground hover:text-foreground',
        'destructive' => 'bg-rose-600 text-white hover:bg-rose-700 shadow-sm active:bg-rose-800',
        'outline' => 'bg-transparent border border-border text-foreground hover:bg-muted',
        default => 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm',
    };

    $sizeClasses = match($size) {
        'sm' => 'text-xs px-3 py-1.5 min-h-[36px] gap-1.5',
        'lg' => 'text-base px-7 py-3 min-h-[52px] gap-2.5',
        'icon' => 'p-2.5 min-h-[44px] min-w-[44px] justify-center',
        default => 'text-sm px-5 py-2.5 min-h-[44px] gap-2',
    };

    $roundedClasses = match($rounded) {
        'lg' => 'rounded-lg',
        'md' => 'rounded-md',
        'none' => 'rounded-none',
        default => 'rounded-full',
    };

    $classes = "{$baseClasses} {$variantClasses} {$sizeClasses} {$roundedClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled || $loading ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right' && !$loading)
            <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
        @endif
    </button>
@endif
