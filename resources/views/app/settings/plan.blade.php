<x-layouts.app title="Plan & Resource Usage" subtitle="Monitor your plan quotas, resource consumption, and tier features" activeNav="settings">
    <div class="space-y-6">
        <!-- Settings Nav Tabs -->
        <x-navigation.settings-nav active="plan" />

        <!-- Current Plan Overview Card -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold font-mono bg-accent/10 text-accent border border-accent/20">
                        {{ $plan ? strtoupper($plan->name) : 'FREE' }} PLAN
                    </span>
                    @if($subscription && $subscription->isTrialing())
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                            Trial: {{ $subscription->daysLeftInTrial() }} days left
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-foreground">Stall Capacity & Usage Quotas</h2>
                <p class="text-xs text-muted-foreground">{{ $plan?->description ?? 'Standard retail point-of-sale capabilities.' }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('app.settings.billing') }}" class="px-4 py-2 text-xs font-semibold rounded-xl border border-border bg-secondary text-foreground hover:bg-muted transition-colors">
                    Manage Billing
                </a>
                <a href="#plans-section" class="px-4 py-2 text-xs font-bold rounded-xl bg-accent text-accent-foreground hover:bg-accent/90 shadow-sm shadow-accent/20">
                    Upgrade Plan &rarr;
                </a>
            </div>
        </div>

        <!-- Resource Usage Progress Bars -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Products Usage -->
            @php $prod = $usage['products']; @endphp
            <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="boxes" class="w-4 h-4 text-muted-foreground"></i>
                        <span class="text-xs font-bold text-foreground">Products</span>
                    </div>
                    @if($prod['warning_level'] === 'reached')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">LIMIT REACHED</span>
                    @elseif($prod['warning_level'] === 'critical')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700">90% CAP</span>
                    @elseif($prod['warning_level'] === 'warning')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">80% USED</span>
                    @endif
                </div>

                <div>
                    <div class="flex items-baseline justify-between text-xs mb-1.5">
                        <span class="font-mono text-lg font-extrabold text-foreground">{{ $prod['used'] }}</span>
                        <span class="font-mono text-xs text-muted-foreground">of {{ $prod['is_unlimited'] ? 'Unlimited (∞)' : $prod['limit'] }} allowed</span>
                    </div>
                    <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all {{ $prod['warning_level'] === 'reached' ? 'bg-rose-600' : ($prod['warning_level'] === 'critical' ? 'bg-rose-500' : ($prod['warning_level'] === 'warning' ? 'bg-amber-500' : 'bg-accent')) }}" style="width: {{ min(100, $prod['percentage']) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Users Usage -->
            @php $usr = $usage['users']; @endphp
            <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-muted-foreground"></i>
                        <span class="text-xs font-bold text-foreground">Staff Accounts</span>
                    </div>
                    @if($usr['warning_level'] === 'reached')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">LIMIT REACHED</span>
                    @endif
                </div>

                <div>
                    <div class="flex items-baseline justify-between text-xs mb-1.5">
                        <span class="font-mono text-lg font-extrabold text-foreground">{{ $usr['used'] }}</span>
                        <span class="font-mono text-xs text-muted-foreground">of {{ $usr['is_unlimited'] ? 'Unlimited (∞)' : $usr['limit'] }} allowed</span>
                    </div>
                    <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all {{ $usr['warning_level'] === 'reached' ? 'bg-rose-600' : 'bg-accent' }}" style="width: {{ min(100, $usr['percentage']) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales Quota -->
            @php $sales = $usage['monthly_sales']; @endphp
            <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-muted-foreground"></i>
                        <span class="text-xs font-bold text-foreground">Monthly Sales</span>
                    </div>
                    @if($sales['warning_level'] === 'reached')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">QUOTA REACHED</span>
                    @endif
                </div>

                <div>
                    <div class="flex items-baseline justify-between text-xs mb-1.5">
                        <span class="font-mono text-lg font-extrabold text-foreground">{{ $sales['used'] }}</span>
                        <span class="font-mono text-xs text-muted-foreground">of {{ $sales['is_unlimited'] ? 'Unlimited (∞)' : $sales['limit'] }} this month</span>
                    </div>
                    <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all {{ $sales['warning_level'] === 'reached' ? 'bg-rose-600' : 'bg-accent' }}" style="width: {{ min(100, $sales['percentage']) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Plans Section -->
        <div id="plans-section" class="space-y-4 pt-4">
            <div>
                <h3 class="text-base font-bold text-foreground">Available Subscription Plans</h3>
                <p class="text-xs text-muted-foreground">Upgrade or scale your stall as your business expands</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($availablePlans as $p)
                    @php $isCurrent = $plan && $plan->id === $p->id; @endphp
                    <div class="bg-white rounded-2xl border {{ $isCurrent ? 'border-accent ring-2 ring-accent/20' : 'border-border/80' }} p-5 flex flex-col justify-between space-y-4 shadow-2xs">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-foreground">{{ $p->name }}</span>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent text-accent-foreground">CURRENT</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-xl font-extrabold font-mono text-foreground">Rs. {{ number_format($p->price) }}</span>
                                <span class="text-[11px] text-muted-foreground">/ month</span>
                            </div>

                            <p class="text-xs text-muted-foreground">{{ $p->description }}</p>

                            <div class="pt-2 border-t border-border/70 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Products:</span>
                                    <span class="font-mono font-semibold">{{ $p->getFeatureLimit('products') ?? 'Unlimited' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Staff Users:</span>
                                    <span class="font-mono font-semibold">{{ $p->getFeatureLimit('users') ?? 'Unlimited' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Monthly Sales:</span>
                                    <span class="font-mono font-semibold">{{ $p->getFeatureLimit('monthly_sales') ?? 'Unlimited' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($isCurrent)
                                <button type="button" disabled class="w-full py-2 text-xs font-semibold rounded-xl bg-secondary text-muted-foreground cursor-not-allowed">
                                    Active Plan
                                </button>
                            @else
                                <form method="POST" action="{{ route('app.settings.billing.upgrade') }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $p->id }}" />
                                    <input type="hidden" name="billing_interval" value="monthly" />
                                    <button type="submit" class="w-full py-2 text-xs font-bold rounded-xl bg-accent text-accent-foreground hover:bg-accent/90 transition-colors shadow-sm">
                                        Select {{ $p->name }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
