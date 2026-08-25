<x-layouts.platform title="SaaS Overview Dashboard" subtitle="Real-time multi-tenant platform metrics and business health" activeNav="dashboard">
    <!-- Top KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Businesses Card -->
        <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Total Businesses</span>
                <span class="p-2 rounded-xl bg-accent/10 text-accent">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-mono text-foreground">{{ number_format($kpis['total_businesses']) }}</p>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-muted-foreground">
                    <span class="text-emerald-700 font-semibold font-mono">{{ $kpis['active_businesses'] }} active</span>
                    <span>&bull;</span>
                    <span class="text-amber-700 font-mono">{{ $kpis['trial_accounts'] }} trial</span>
                    @if($kpis['suspended_businesses'] > 0)
                        <span>&bull;</span>
                        <span class="text-rose-700 font-mono">{{ $kpis['suspended_businesses'] }} suspended</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Paid Subscriptions & MRR -->
        <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Monthly Recurring (MRR)</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-mono text-emerald-700">Rs. {{ number_format($kpis['mrr'], 2) }}</p>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-muted-foreground">
                    <span>ARR: <strong class="font-mono text-foreground font-semibold">Rs. {{ number_format($kpis['arr'], 2) }}</strong></span>
                    <span>&bull;</span>
                    <span class="font-mono text-emerald-700 font-semibold">{{ $kpis['paid_subscriptions'] }} paid subs</span>
                </div>
            </div>
        </div>

        <!-- Platform Users -->
        <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Platform Users</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-700">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-mono text-foreground">{{ number_format($kpis['total_users']) }}</p>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-muted-foreground">
                    <span class="text-emerald-700 font-semibold font-mono">{{ $kpis['active_users'] }} active</span>
                    <span>&bull;</span>
                    <span>ARPU: <strong class="font-mono text-foreground">Rs. {{ number_format($kpis['arpu'], 2) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Gross Sales Processed (GMV) -->
        <div class="p-5 rounded-2xl bg-white border border-border/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Gross Volume (GMV)</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-700">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-mono text-foreground">Rs. {{ number_format($kpis['gmv'], 2) }}</p>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-muted-foreground">
                    <span class="font-mono">{{ number_format($kpis['total_sales_count']) }} transactions</span>
                    <span>&bull;</span>
                    <span>Conv: <strong class="font-mono text-emerald-700">{{ $kpis['conversion_rate'] }}%</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Distribution & New Growth -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Plan Breakdown -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-foreground">Active Plan Distribution</h3>
                <a href="{{ route('platform.plans.index') }}" class="text-xs text-accent font-semibold hover:underline">Manage Plans</a>
            </div>

            <div class="space-y-3">
                @foreach($planDistribution as $plan)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">{{ $plan['name'] }}</span>
                            <span class="font-mono text-muted-foreground">{{ $plan['count'] }} businesses ({{ $plan['share_percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-secondary/80 rounded-full h-2 overflow-hidden">
                            <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ max(4, $plan['share_percentage']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Growth Velocity -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-foreground">Platform Growth & Velocity</h3>
                <span class="text-xs text-muted-foreground">Period: 30 Days</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-secondary/50 border border-border space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">New Stalls (7 Days)</span>
                    <p class="text-xl font-bold font-mono text-foreground">+{{ $kpis['new_businesses_7d'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-secondary/50 border border-border space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">New Stalls (30 Days)</span>
                    <p class="text-xl font-bold font-mono text-foreground">+{{ $kpis['new_businesses_30d'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-secondary/50 border border-border space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">Cancelled / Churned</span>
                    <p class="text-xl font-bold font-mono text-rose-700">{{ $kpis['churn_count'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registered Businesses Table -->
    <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-border/70 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-foreground">Recently Registered Businesses</h3>
                <p class="text-xs text-muted-foreground">Latest stalls onboarded into the ORVIK platform</p>
            </div>
            <a href="{{ route('platform.businesses') }}" class="text-xs font-semibold text-accent hover:underline flex items-center gap-1">
                <span>View All Businesses</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Business Name</th>
                        <th class="px-5 py-3.5">Owner</th>
                        <th class="px-5 py-3.5">Current Plan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Created</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60 text-foreground">
                    @forelse($recentBusinesses as $b)
                        @php
                            $sub = $b->currentSubscription();
                        @endphp
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="px-5 py-3.5 font-bold text-foreground">
                                {{ $b->name }}
                                <span class="block text-[10px] text-muted-foreground font-mono">ID: #{{ $b->id }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-foreground">{{ $b->owner?->name ?? '—' }}</span>
                                <span class="block text-[10px] text-muted-foreground">{{ $b->owner?->email ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $sub && $sub->isTrialing() ? 'bg-amber-100 text-amber-800' : 'bg-secondary text-foreground border border-border' }}">
                                    {{ $sub?->plan?->name ?? 'Free' }}
                                    @if($sub && $sub->isTrialing())
                                        (Trial)
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($b->status === 'suspended')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">SUSPENDED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">ACTIVE</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground font-mono">
                                {{ $b->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('platform.businesses.show', $b->id) }}" class="px-2.5 py-1 rounded-lg bg-secondary text-foreground hover:bg-muted text-xs font-semibold border border-border">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-muted-foreground">
                                No registered businesses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.platform>
