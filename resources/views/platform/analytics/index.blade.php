<x-layouts.platform title="SaaS Growth & Monetization Analytics" subtitle="Financial health, unit economics, conversion, and churn tracking" activeNav="analytics">
    <div class="space-y-6">
        <!-- SaaS KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-2">
                <span class="text-[11px] font-semibold text-muted-foreground uppercase">Monthly Recurring (MRR)</span>
                <p class="text-2xl font-extrabold font-mono text-emerald-700">Rs. {{ number_format($kpis['mrr'], 2) }}</p>
                <p class="text-[11px] text-muted-foreground">ARR: <strong class="font-mono text-foreground font-semibold">Rs. {{ number_format($kpis['arr'], 2) }}</strong></p>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-2">
                <span class="text-[11px] font-semibold text-muted-foreground uppercase">Average Revenue / Business (ARPU)</span>
                <p class="text-2xl font-extrabold font-mono text-foreground">Rs. {{ number_format($kpis['arpu'], 2) }}</p>
                <p class="text-[11px] text-muted-foreground">Across <strong class="font-mono text-foreground font-semibold">{{ $kpis['paid_subscriptions'] }}</strong> paying stalls</p>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-2">
                <span class="text-[11px] font-semibold text-muted-foreground uppercase">Trial-to-Paid Conversion</span>
                <p class="text-2xl font-extrabold font-mono text-emerald-700">{{ $kpis['conversion_rate'] }}%</p>
                <p class="text-[11px] text-muted-foreground">{{ $kpis['trial_accounts'] }} active trials ongoing</p>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-2">
                <span class="text-[11px] font-semibold text-muted-foreground uppercase">Total Processed GMV</span>
                <p class="text-2xl font-extrabold font-mono text-foreground">Rs. {{ number_format($kpis['gmv'], 2) }}</p>
                <p class="text-[11px] text-muted-foreground">{{ number_format($kpis['total_sales_count']) }} retail sales</p>
            </div>
        </div>

        <!-- Plan Breakdown Matrix -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-foreground">Subscription Tiers Breakdown</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($planDistribution as $plan)
                    <div class="p-4 rounded-xl bg-secondary/40 border border-border space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-foreground text-sm">{{ $plan['name'] }}</span>
                            <span class="font-mono text-xs font-semibold text-muted-foreground">Rs. {{ number_format($plan['price']) }}/mo</span>
                        </div>
                        <p class="text-xl font-extrabold font-mono text-accent">{{ $plan['count'] }} stalls</p>
                        <div class="w-full bg-secondary rounded-full h-1.5 overflow-hidden">
                            <div class="bg-accent h-1.5 rounded-full" style="width: {{ max(4, $plan['share_percentage']) }}%"></div>
                        </div>
                        <p class="text-[10px] text-muted-foreground text-right font-mono">{{ $plan['share_percentage'] }}% of customer base</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.platform>
