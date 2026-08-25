<section class="relative overflow-hidden pt-12 pb-24 md:pt-20 md:pb-32 min-h-[92vh] flex flex-col justify-center items-center">
    <!-- Background Video Treatment -->
    <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <video
            autoplay
            loop
            muted
            playsinline
            preload="auto"
            class="absolute inset-0 w-full h-full object-cover opacity-35"
        >
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260319_015952_e1deeb12-8fb7-4071-a42a-60779fc64ab6.mp4" type="video/mp4">
        </video>
        <!-- Very Subtle Gradient Overlay preserving natural brightness -->
        <div class="absolute inset-0 bg-gradient-to-b from-white/70 via-white/50 to-white/90"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 mx-auto max-w-7xl px-6 md:px-12 lg:px-20 text-center flex flex-col items-center w-full">
        <!-- Hero Badge -->
        <div class="inline-flex items-center gap-1.5 rounded-full border border-border bg-white/90 px-4 py-1.5 text-sm font-body text-foreground shadow-2xs mb-6 select-none animate-fade-up-1 backdrop-blur-xs">
            <span class="text-accent text-xs">&#10022;</span>
            <span class="font-medium">Simple stall management, built for speed</span>
        </div>

        <!-- Hero Headline (Instrument Serif) -->
        <h1 class="text-center font-display text-5xl md:text-6xl lg:text-[5.25rem] leading-[0.95] tracking-tight max-w-xl text-foreground animate-fade-up-2">
            The <span class="italic font-normal text-accent">Simpler</span> Way to Run Your Stall
        </h1>

        <!-- Hero Subheadline (Inter) -->
        <p class="mt-4 text-center text-base md:text-lg max-w-[650px] leading-relaxed font-body text-muted-foreground animate-fade-up-3">
            Manage sales, inventory, stock levels, and business reports from one simple system built for busy stalls.
        </p>

        <!-- Hero CTA Buttons -->
        <div class="mt-6 flex items-center justify-center gap-3 animate-fade-up-4">
            <x-ui.button variant="primary" size="lg" rounded="full" :href="route('register')">
                Get Started
            </x-ui.button>

            <x-ui.button variant="secondary" size="lg" rounded="full" href="#how-it-works">
                See How It Works
            </x-ui.button>
        </div>

        <!-- Coded Dashboard Preview Container -->
        <div class="w-full max-w-5xl mt-12 sm:mt-16 animate-fade-up-5">
            <x-dashboard.preview />
        </div>
    </div>
</section>
