<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plan Comparison Matrix — ORVIK Store</title>
    <meta name="description" content="Detailed side-by-side feature and quota comparison of all ORVIK Store SaaS tiers.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-body antialiased selection:bg-accent/20 selection:text-accent">
    <!-- Marketing Navbar -->
    <x-navigation.marketing-navbar />

    <main class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <a href="{{ route('pricing') }}" class="text-xs text-muted-foreground hover:text-foreground inline-flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Back to Pricing</span>
            </a>
            <h1 class="text-3xl md:text-5xl font-normal font-serif tracking-tight text-foreground">
                Compare All Features & Limits
            </h1>
            <p class="text-sm text-muted-foreground">Find the exact tier tailored to your retail workflow.</p>
        </div>

        <div class="bg-white rounded-3xl border border-border/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-secondary/70 border-b border-border text-foreground">
                            <th class="px-6 py-4 font-bold text-sm">Capability / Feature</th>
                            @foreach($plans as $plan)
                                <th class="px-6 py-4 text-center">
                                    <p class="font-bold text-sm text-foreground">{{ $plan->name }}</p>
                                    <p class="font-mono text-[11px] text-muted-foreground">Rs. {{ number_format($plan->price) }}/mo</p>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        @foreach($features as $feature)
                            <tr class="hover:bg-muted/20 transition-colors">
                                <td class="px-6 py-3.5">
                                    <p class="font-semibold text-foreground">{{ $feature->name }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ $feature->description }}</p>
                                </td>
                                @foreach($plans as $plan)
                                    @php
                                        $pivot = $plan->features->firstWhere('id', $feature->id)?->pivot;
                                        $isEnabled = $pivot ? (bool)$pivot->enabled : false;
                                        $limitVal = $pivot ? $pivot->limit_value : null;
                                    @endphp
                                    <td class="px-6 py-3.5 text-center font-mono">
                                        @if($feature->type === 'BOOLEAN')
                                            @if($isEnabled)
                                                <i data-lucide="check" class="w-4 h-4 text-emerald-600 mx-auto"></i>
                                            @else
                                                <span class="text-muted-foreground">—</span>
                                            @endif
                                        @else
                                            @if($limitVal === null)
                                                <span class="font-bold text-accent">Unlimited (∞)</span>
                                            @else
                                                <span class="font-bold text-foreground">{{ number_format($limitVal) }}</span>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Public Footer -->
    <footer class="border-t border-border bg-white py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>&copy; {{ date('Y') }} ORVIK Store. All rights reserved.</p>
        <div class="flex items-center gap-6 font-medium">
            <a href="{{ route('pricing') }}" class="hover:text-foreground">Pricing</a>
            <a href="{{ route('privacy') }}" class="hover:text-foreground">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-foreground">Terms of Service</a>
            <a href="{{ route('cookie.policy') }}" class="hover:text-foreground">Cookie Policy</a>
        </div>
    </footer>
</body>
</html>
