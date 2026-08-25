<x-layouts.platform title="Plans & Quotas" subtitle="Database-driven SaaS subscription tiers and feature matrices" activeNav="plans">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-foreground">Configured Subscription Plans</h2>
            <p class="text-xs text-muted-foreground">Manage pricing, intervals, feature access, and maximum resource limits</p>
        </div>

        <a href="{{ route('platform.plans.create') }}" class="px-4 py-2 bg-accent text-accent-foreground font-bold text-xs rounded-xl hover:bg-accent/90 flex items-center gap-1.5 shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Plan</span>
        </a>
    </div>

    <!-- Plans Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl border border-border/80 shadow-2xs p-6 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $plan->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-muted text-muted-foreground' }}">
                            {{ $plan->is_active ? 'ACTIVE' : 'DRAFT' }}
                        </span>
                        <span class="text-xs font-mono text-muted-foreground">{{ $plan->subscriptions_count }} subscribers</span>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-foreground">{{ $plan->name }}</h3>
                        <p class="text-xs text-muted-foreground mt-1">{{ $plan->description }}</p>
                    </div>

                    <div class="pt-2">
                        <span class="text-2xl font-extrabold font-mono text-foreground">Rs. {{ number_format($plan->price) }}</span>
                        <span class="text-xs text-muted-foreground">/ month</span>
                        @if($plan->yearly_price)
                            <p class="text-[11px] text-emerald-700 font-mono mt-0.5">Rs. {{ number_format($plan->yearly_price) }} / year</p>
                        @endif
                    </div>

                    <!-- Key Limits Summary -->
                    <div class="pt-3 border-t border-border space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Products:</span>
                            <span class="font-mono font-bold">{{ $plan->getFeatureLimit('products') ?? 'Unlimited' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Staff Users:</span>
                            <span class="font-mono font-bold">{{ $plan->getFeatureLimit('users') ?? 'Unlimited' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Monthly Sales:</span>
                            <span class="font-mono font-bold">{{ $plan->getFeatureLimit('monthly_sales') ?? 'Unlimited' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="space-y-2 pt-4 border-t border-border">
                    <a href="{{ route('platform.plans.features', $plan->id) }}" class="w-full py-2 px-3 text-center text-xs font-bold rounded-xl bg-secondary text-foreground hover:bg-muted flex items-center justify-center gap-1.5">
                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                        <span>Feature Matrix</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('platform.plans.edit', $plan->id) }}" class="w-1/2 py-1.5 text-center text-xs font-semibold rounded-lg border border-border text-foreground hover:bg-secondary">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('platform.plans.destroy', $plan->id) }}" class="w-1/2" onsubmit="return confirm('Archive plan {{ $plan->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-1.5 text-center text-xs font-semibold rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50">
                                Archive
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.platform>
