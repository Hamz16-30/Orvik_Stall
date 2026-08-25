@props([
    'type' => 'spinner', // spinner, skeleton, card-skeleton, table-skeleton
    'size' => 'md',
    'text' => null,
])

@php
    $spinnerSizes = match($size) {
        'sm' => 'w-4 h-4 border-2',
        'lg' => 'w-8 h-8 border-3',
        default => 'w-6 h-6 border-2',
    };
@endphp

@if($type === 'spinner')
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-2 p-4 text-muted-foreground']) }}>
        <div class="animate-spin rounded-full border-accent border-t-transparent {{ $spinnerSizes }}"></div>
        @if($text)
            <span class="text-xs font-medium">{{ $text }}</span>
        @endif
    </div>
@elseif($type === 'skeleton')
    <div {{ $attributes->merge(['class' => 'animate-pulse bg-muted rounded-lg h-4 w-full']) }}></div>
@elseif($type === 'card-skeleton')
    <div {{ $attributes->merge(['class' => 'animate-pulse p-6 bg-white border border-border rounded-xl space-y-4 shadow-sm']) }}>
        <div class="h-4 bg-muted rounded w-1/3"></div>
        <div class="space-y-2">
            <div class="h-3 bg-muted rounded"></div>
            <div class="h-3 bg-muted rounded w-5/6"></div>
        </div>
    </div>
@elseif($type === 'table-skeleton')
    <div {{ $attributes->merge(['class' => 'animate-pulse space-y-3 p-4 bg-white border border-border rounded-xl']) }}>
        <div class="h-6 bg-muted rounded w-full"></div>
        <div class="h-4 bg-muted/60 rounded w-full"></div>
        <div class="h-4 bg-muted/60 rounded w-full"></div>
        <div class="h-4 bg-muted/60 rounded w-full"></div>
    </div>
@endif
