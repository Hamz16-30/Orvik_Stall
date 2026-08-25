@props([
    'size' => 'md',
    'href' => '/',
    'showText' => true,
    'customClass' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'text-lg gap-1.5',
        'lg' => 'text-2xl gap-2.5',
        'xl' => 'text-3xl gap-3',
        default => 'text-xl gap-2',
    };
    $markClasses = match($size) {
        'sm' => 'text-accent text-base',
        'lg' => 'text-accent text-2xl',
        'xl' => 'text-accent text-3xl',
        default => 'text-accent text-lg',
    };
@endphp

<a href="{{ $href }}" class="inline-flex items-center font-bold tracking-tight text-foreground select-none transition-opacity hover:opacity-90 {{ $sizeClasses }} {{ $customClass }}">
    <span class="inline-block transition-transform duration-300 group-hover:rotate-12 {{ $markClasses }}">&#10022;</span>
    @if($showText)
        <span class="font-bold tracking-tight text-foreground">ORVIK <span class="font-medium text-foreground/80">Store</span></span>
    @endif
</a>
