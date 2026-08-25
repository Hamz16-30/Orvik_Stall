<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simple, Transparent Pricing — ORVIK Store</title>
    <meta name="description" content="Affordable SaaS plans for retail stalls, solo vendors, and growing store chains. Start with a 14-day free trial.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-body antialiased selection:bg-accent/20 selection:text-accent">
    <!-- Marketing Navbar -->
    <x-navigation.marketing-navbar />

    <main class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-16">
        <!-- Hero Header -->
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-accent/10 border border-accent/20 text-accent text-xs font-semibold tracking-wide uppercase">
                Flexible Monetization & Plans
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal font-serif tracking-tight text-foreground">
                Predictable plans for stalls of any scale.
            </h1>
            <p class="text-base md:text-lg text-muted-foreground">
                Start your 14-day free trial today. No credit card required, cancel or change tiers anytime.
            </p>
        </div>

        <!-- Monthly / Yearly Switcher (Alpine) -->
        <div x-data="{ billing: 'monthly' }" class="space-y-12">
            <div class="flex items-center justify-center gap-3">
                <span class="text-xs font-semibold" :class="billing === 'monthly' ? 'text-foreground' : 'text-muted-foreground'">Monthly</span>
                <button
                    type="button"
                    @click="billing = billing === 'monthly' ? 'yearly' : 'monthly'"
                    class="w-12 h-6 rounded-full bg-secondary border border-border p-0.5 transition-colors relative focus:outline-none"
                    :class="billing === 'yearly' ? 'bg-accent' : 'bg-secondary'"
                >
                    <div class="w-5 h-5 rounded-full bg-white shadow-xs transition-transform transform" :class="billing === 'yearly' ? 'translate-x-6' : 'translate-x-0'"></div>
                </button>
                <span class="text-xs font-semibold flex items-center gap-1.5" :class="billing === 'yearly' ? 'text-foreground' : 'text-muted-foreground'">
                    <span>Yearly</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Save ~17%</span>
                </span>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($plans as $plan)
                    <div class="bg-white rounded-3xl border {{ $plan->slug === 'starter' ? 'border-accent ring-2 ring-accent/30 shadow-lg' : 'border-border/80 shadow-2xs' }} p-6 flex flex-col justify-between space-y-6 relative overflow-hidden">
                        @if($plan->slug === 'starter')
                            <div class="absolute top-0 right-0 bg-accent text-accent-foreground text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-bl-xl">
                                POPULAR
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold text-foreground">{{ $plan->name }}</h3>
                                <p class="text-xs text-muted-foreground mt-1">{{ $plan->description }}</p>
                            </div>

                            <div class="pt-2">
                                <div x-show="billing === 'monthly'">
                                    <span class="text-3xl font-extrabold font-mono text-foreground">Rs. {{ number_format($plan->price) }}</span>
                                    <span class="text-xs text-muted-foreground">/ month</span>
                                </div>
                                <div x-show="billing === 'yearly'" style="display: none;">
                                    <span class="text-3xl font-extrabold font-mono text-foreground">Rs. {{ number_format($plan->yearly_price ? $plan->yearly_price / 12 : $plan->price) }}</span>
                                    <span class="text-xs text-muted-foreground">/ month</span>
                                    @if($plan->yearly_price)
                                        <p class="text-[11px] text-muted-foreground font-mono mt-0.5">Billed Rs. {{ number_format($plan->yearly_price) }} annually</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Features List -->
                            <ul class="space-y-2.5 pt-4 border-t border-border text-xs text-foreground">
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                    <span><strong>{{ $plan->getFeatureLimit('products') ?? 'Unlimited' }}</strong> Products</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                    <span><strong>{{ $plan->getFeatureLimit('users') ?? 'Unlimited' }}</strong> Staff User(s)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                    <span><strong>{{ $plan->getFeatureLimit('monthly_sales') ?? 'Unlimited' }}</strong> Monthly Sales</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                    <span>Fast Touch POS & Barcode Scanner</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    @if($plan->hasFeature('excel_export'))
                                        <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                        <span>Excel (.xls) Exports</span>
                                    @else
                                        <i data-lucide="x" class="w-4 h-4 text-muted-foreground/50 shrink-0"></i>
                                        <span class="text-muted-foreground">No Excel Export</span>
                                    @endif
                                </li>
                                <li class="flex items-center gap-2">
                                    @if($plan->hasFeature('advanced_reports'))
                                        <i data-lucide="check" class="w-4 h-4 text-accent shrink-0"></i>
                                        <span>P&L & Profitability Waterfall</span>
                                    @else
                                        <i data-lucide="x" class="w-4 h-4 text-muted-foreground/50 shrink-0"></i>
                                        <span class="text-muted-foreground">Basic Reports</span>
                                    @endif
                                </li>
                            </ul>
                        </div>

                        <div>
                            @auth
                                <a href="{{ route('app.settings.plan') }}" class="w-full py-2.5 px-4 text-center text-xs font-bold rounded-xl {{ $plan->slug === 'starter' ? 'bg-accent text-accent-foreground hover:bg-accent/90' : 'bg-secondary text-foreground hover:bg-muted' }} block transition-all shadow-xs">
                                    Select Plan
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="w-full py-2.5 px-4 text-center text-xs font-bold rounded-xl {{ $plan->slug === 'starter' ? 'bg-accent text-accent-foreground hover:bg-accent/90' : 'bg-secondary text-foreground hover:bg-muted' }} block transition-all shadow-xs">
                                    Start 14-Day Trial
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Plan Comparison CTA -->
        <div class="text-center pt-8">
            <a href="{{ route('pricing.compare') }}" class="inline-flex items-center gap-2 text-sm font-bold text-accent hover:underline">
                <span>View Complete Feature Comparison Matrix</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </main>

    <!-- Public Footer -->
    <footer class="border-t border-border bg-white py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>&copy; {{ date('Y') }} ORVIK Store. All rights reserved.</p>
        <div class="flex items-center gap-6 font-medium">
            <a href="{{ route('pricing') }}" class="hover:text-foreground">Pricing</a>
            <a href="{{ route('pricing.compare') }}" class="hover:text-foreground">Compare Plans</a>
            <a href="{{ route('privacy') }}" class="hover:text-foreground">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-foreground">Terms of Service</a>
            <a href="{{ route('cookie.policy') }}" class="hover:text-foreground">Cookie Policy</a>
        </div>
    </footer>
</body>
</html>
