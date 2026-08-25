<x-layouts.marketing title="404 — Page Not Found">
    <div class="min-h-[70vh] flex items-center justify-center px-6 py-16">
        <div class="max-w-md w-full text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-secondary text-muted-foreground border border-border flex items-center justify-center mx-auto shadow-2xs">
                <i data-lucide="compass" class="w-7 h-7"></i>
            </div>

            <h1 class="text-3xl font-display font-normal text-foreground tracking-tight">We couldn't find that page</h1>
            <p class="text-sm text-muted-foreground leading-relaxed">
                The URL you requested does not exist or has been moved.
            </p>

            <div class="pt-4 flex items-center justify-center gap-3">
                <x-ui.button variant="primary" size="md" rounded="full" :href="route('app.dashboard')">
                    Back to Dashboard
                </x-ui.button>
                <x-ui.button variant="secondary" size="md" rounded="full" :href="route('home')">
                    Return Home
                </x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.marketing>
