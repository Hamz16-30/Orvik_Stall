@props([
    'title' => 'Something went wrong',
    'message' => 'An unexpected error occurred while processing your request. Please try again or contact support if the issue persists.',
    'retryUrl' => null,
    'retryClick' => null,
    'homeUrl' => '/',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 text-center rounded-2xl border border-rose-200/80 bg-rose-50/30 my-4 max-w-md mx-auto']) }}>
    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 mb-3">
        <i data-lucide="alert-octagon" class="w-6 h-6"></i>
    </div>

    <h4 class="text-base font-semibold text-foreground tracking-tight">{{ $title }}</h4>
    <p class="text-xs text-muted-foreground mt-1.5 mb-5 leading-relaxed">{{ $message }}</p>

    <div class="flex items-center gap-3">
        @if($retryUrl)
            <x-ui.button variant="secondary" size="sm" :href="$retryUrl">
                Try Again
            </x-ui.button>
        @elseif($retryClick)
            <x-ui.button variant="secondary" size="sm" @click="{{ $retryClick }}">
                Try Again
            </x-ui.button>
        @endif

        <x-ui.button variant="primary" size="sm" :href="$homeUrl">
            Return Home
        </x-ui.button>
    </div>
</div>
