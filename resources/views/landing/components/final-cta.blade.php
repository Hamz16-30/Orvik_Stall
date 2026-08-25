<section class="py-20 md:py-28 bg-white text-center">
    <div class="mx-auto max-w-4xl px-6 md:px-12">
        <div class="inline-flex items-center gap-1.5 rounded-full border border-border bg-secondary px-3.5 py-1.5 text-xs font-semibold text-foreground/80 mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
            <span>Built for Modern Stalls & Market Retail</span>
        </div>

        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-display font-normal text-foreground tracking-tight max-w-2xl mx-auto leading-tight">
            Ready to simplify your stall?
        </h2>

        <p class="mt-4 text-base sm:text-lg text-muted-foreground max-w-xl mx-auto leading-relaxed">
            Manage sales, inventory, and your daily business from one simple system designed to save hours every week.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <x-ui.button variant="primary" size="lg" rounded="full" :href="route('register')">
                Get Started
            </x-ui.button>

            <x-ui.button variant="secondary" size="lg" rounded="full" :href="route('app.dashboard')">
                Explore Demo Register
            </x-ui.button>
        </div>
    </div>
</section>
