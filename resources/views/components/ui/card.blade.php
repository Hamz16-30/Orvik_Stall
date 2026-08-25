@props([
    'title' => null,
    'description' => null,
    'headerActions' => null,
    'footer' => null,
    'padding' => 'p-6',
    'shadow' => 'shadow-sm',
    'border' => 'border border-border/80',
    'bg' => 'bg-white',
])

<div {{ $attributes->merge(['class' => "{$bg} {$border} {$shadow} rounded-xl overflow-hidden"]) }}>
    @if($title || $description || $headerActions || isset($header))
        <div class="px-6 py-4 border-b border-border/60 flex items-center justify-between gap-4">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    @if($title)
                        <h3 class="text-base font-semibold text-foreground tracking-tight">{{ $title }}</h3>
                    @endif
                    @if($description)
                        <p class="text-xs text-muted-foreground mt-0.5">{{ $description }}</p>
                    @endif
                </div>
                @if($headerActions)
                    <div class="flex items-center gap-2">
                        {{ $headerActions }}
                    </div>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-6 py-3.5 bg-muted/40 border-t border-border/60 text-xs text-muted-foreground">
            {{ $footer }}
        </div>
    @endif
</div>
