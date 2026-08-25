<section id="pricing" class="py-20 md:py-28 bg-secondary/30 border-b border-border/60">
    <div class="mx-auto max-w-7xl px-6 md:px-12 lg:px-20">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-semibold uppercase tracking-wider text-accent">Transparent Plans</span>
            <h2 class="mt-2 text-3xl sm:text-4xl md:text-5xl font-display font-normal text-foreground tracking-tight">
                Simple pricing. No complicated setup.
            </h2>
            <p class="mt-4 text-base text-muted-foreground leading-relaxed">
                Straightforward plans tailored for single stalls, multi-counter markets, and growing retail operators.
            </p>
        </div>

        <!-- Pricing Notice Card -->
        <div class="max-w-xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-border/80 shadow-sm text-center">
            <div class="w-12 h-12 rounded-full bg-accent/10 text-accent flex items-center justify-center mx-auto mb-4">
                <i data-lucide="tag" class="w-6 h-6"></i>
            </div>

            <h3 class="text-xl font-bold text-foreground tracking-tight">Early Access Registration</h3>
            <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                Official subscription pricing tiers will be announced soon. Sign up for early access today to lock in special founder stall rates.
            </p>

            <div class="mt-8 pt-6 border-t border-border/60 flex flex-col sm:flex-row items-center justify-center gap-4">
                <x-ui.button variant="primary" size="lg" rounded="full" :href="route('register')">
                    Get Started with Early Access
                </x-ui.button>
            </div>
        </div>
    </div>
</section>
