@props([
    'id' => 'modal-' . uniqid(),
    'title' => null,
    'description' => null,
    'maxWidth' => 'md',
])

@php
    $widthClasses = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $id }}') show = true"
    x-on:close-modal.window="if ($event.detail === '{{ $id }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="relative z-50"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-foreground/40 backdrop-blur-sm"
        x-on:click="show = false"
    ></div>

    <!-- Modal Dialog -->
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 flex min-h-full items-end sm:items-center justify-center">
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $widthClasses }} overflow-hidden rounded-2xl bg-white shadow-2xl border border-border/80 text-left transition-all"
            x-on:click.stop
        >
            <div class="px-6 py-5 border-b border-border/60 flex items-center justify-between">
                <div>
                    @if($title)
                        <h3 class="text-base font-semibold text-foreground tracking-tight">{{ $title }}</h3>
                    @endif
                    @if($description)
                        <p class="text-xs text-muted-foreground mt-0.5">{{ $description }}</p>
                    @endif
                </div>
                <button
                    type="button"
                    x-on:click="show = false"
                    class="rounded-full p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="px-6 py-4 bg-muted/30 border-t border-border/60 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
