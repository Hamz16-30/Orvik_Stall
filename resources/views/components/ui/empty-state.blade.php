@props([
    'icon' => 'folder-open',
    'title' => 'No items found',
    'description' => 'Get started by creating your first item.',
    'actionText' => null,
    'actionHref' => null,
    'actionClick' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 text-center rounded-2xl border border-dashed border-border bg-muted/20 my-4']) }}>
    <div class="w-12 h-12 rounded-full bg-secondary flex items-center justify-center text-muted-foreground mb-3">
        <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
    </div>

    <h4 class="text-base font-semibold text-foreground tracking-tight">{{ $title }}</h4>
    <p class="text-xs text-muted-foreground max-w-sm mt-1 mb-4">{{ $description }}</p>

    @if(isset($action))
        {{ $action }}
    @elseif($actionText)
        @if($actionHref)
            <x-ui.button variant="primary" size="sm" :href="$actionHref">
                {{ $actionText }}
            </x-ui.button>
        @else
            <x-ui.button variant="primary" size="sm" @click="{{ $actionClick }}">
                {{ $actionText }}
            </x-ui.button>
        @endif
    @endif
</div>
