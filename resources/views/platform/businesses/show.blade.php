<x-layouts.platform title="Manage Business: {{ $business->name }}" subtitle="Stall ID #{{ $business->id }} &bull; Account Administration" activeNav="businesses">
    <div class="space-y-6">
        <!-- Top Status Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-white border border-border/80 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-primary text-primary-foreground font-bold flex items-center justify-center text-lg shrink-0">
                    {{ strtoupper(substr($business->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-foreground">{{ $business->name }}</h2>
                        @if($business->status === 'suspended')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">SUSPENDED</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">ACTIVE</span>
                        @endif
                    </div>
                    <p class="text-xs text-muted-foreground">Owner: <strong class="text-foreground">{{ $business->owner?->name }}</strong> ({{ $business->owner?->email }})</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                @if($business->status === 'suspended')
                    <form method="POST" action="{{ route('platform.businesses.unsuspend', $business->id) }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Unsuspend Business</span>
                        </button>
                    </form>
                @else
                    <!-- Suspend Button triggering inline form -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 font-bold text-xs flex items-center gap-1.5 transition-colors">
                            <i data-lucide="ban" class="w-4 h-4"></i>
                            <span>Suspend Stall</span>
                        </button>

                        <div x-show="open" @click.outside="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-border">
                                <h3 class="text-base font-bold text-foreground">Suspend Business: {{ $business->name }}</h3>
                                <p class="text-xs text-muted-foreground">This will immediately block operational actions for this stall while retaining all their data.</p>
                                
                                <form method="POST" action="{{ route('platform.businesses.suspend', $business->id) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-semibold text-foreground mb-1">Suspension Reason</label>
                                        <textarea name="reason" rows="3" required placeholder="Reason for suspension (e.g. Terms violation, non-payment)..." class="w-full p-2.5 text-xs rounded-xl border border-border bg-background text-foreground"></textarea>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" @click="open = false" class="px-3 py-2 text-xs text-muted-foreground hover:text-foreground font-semibold">Cancel</button>
                                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700">Confirm Suspension</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Plan & Subscription Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Current Subscription & Change Plan -->
            <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-foreground">Subscription & Plan</h3>
                
                <div class="p-4 rounded-xl bg-secondary/50 border border-border space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Current Plan:</span>
                        <span class="font-bold text-foreground">{{ $currentSubscription?->plan?->name ?? 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Status:</span>
                        <span class="font-mono font-bold uppercase text-accent">{{ $currentSubscription?->status ?? 'Inactive' }}</span>
                    </div>
                    @if($currentSubscription && $currentSubscription->trial_ends_at)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground">Trial Ends:</span>
                            <span class="font-mono text-foreground">{{ $currentSubscription->trial_ends_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($currentSubscription && $currentSubscription->ends_at)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground">Renewal / End Date:</span>
                            <span class="font-mono text-foreground">{{ $currentSubscription->ends_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Change Plan Form -->
                <form method="POST" action="{{ route('platform.businesses.plan', $business->id) }}" class="space-y-3 pt-2 border-t border-border">
                    @csrf
                    <label class="block text-xs font-semibold text-foreground">Change Assigned Plan</label>
                    <select name="plan_id" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ $currentSubscription && $currentSubscription->plan_id === $p->id ? 'selected' : '' }}>
                                {{ $p->name }} (Rs. {{ number_format($p->price) }}/mo)
                            </option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2">
                        <select name="billing_interval" class="w-1/2 px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>

                        <button type="submit" class="w-1/2 px-3 py-2 text-xs font-bold rounded-xl bg-accent text-accent-foreground hover:bg-accent/90">
                            Apply Plan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Resource Usage & Plan Quotas -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-foreground">Resource Usage & Quotas</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Products -->
                    <div class="p-4 rounded-xl border border-border/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">Products</span>
                            <span class="font-mono font-bold">{{ $usage['products']['used'] }} / {{ $usage['products']['is_unlimited'] ? '∞' : $usage['products']['limit'] }}</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2">
                            <div class="h-2 rounded-full {{ $usage['products']['percentage'] >= 100 ? 'bg-rose-500' : ($usage['products']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-accent') }}" style="width: {{ min(100, $usage['products']['percentage']) }}%"></div>
                        </div>
                    </div>

                    <!-- Users -->
                    <div class="p-4 rounded-xl border border-border/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">Staff Users</span>
                            <span class="font-mono font-bold">{{ $usage['users']['used'] }} / {{ $usage['users']['is_unlimited'] ? '∞' : $usage['users']['limit'] }}</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2">
                            <div class="h-2 rounded-full {{ $usage['users']['percentage'] >= 100 ? 'bg-rose-500' : ($usage['users']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-accent') }}" style="width: {{ min(100, $usage['users']['percentage']) }}%"></div>
                        </div>
                    </div>

                    <!-- Monthly Sales -->
                    <div class="p-4 rounded-xl border border-border/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">Monthly Sales</span>
                            <span class="font-mono font-bold">{{ $usage['monthly_sales']['used'] }} / {{ $usage['monthly_sales']['is_unlimited'] ? '∞' : $usage['monthly_sales']['limit'] }}</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2">
                            <div class="h-2 rounded-full {{ $usage['monthly_sales']['percentage'] >= 100 ? 'bg-rose-500' : ($usage['monthly_sales']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-accent') }}" style="width: {{ min(100, $usage['monthly_sales']['percentage']) }}%"></div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="p-4 rounded-xl border border-border/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">Customers</span>
                            <span class="font-mono font-bold">{{ $usage['customers']['used'] }} / {{ $usage['customers']['is_unlimited'] ? '∞' : $usage['customers']['limit'] }}</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2">
                            <div class="h-2 rounded-full {{ $usage['customers']['percentage'] >= 100 ? 'bg-rose-500' : ($usage['customers']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-accent') }}" style="width: {{ min(100, $usage['customers']['percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspension History Log -->
        @if($business->suspensions->isNotEmpty())
            <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
                <div class="p-4 border-b border-border/70">
                    <h3 class="text-sm font-bold text-foreground">Suspension & Audit History</h3>
                </div>
                <div class="divide-y divide-border/60 text-xs">
                    @foreach($business->suspensions as $sus)
                        <div class="p-4 flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-rose-800">Suspended on {{ $sus->suspended_at->format('M d, Y H:i') }}</p>
                                <p class="text-muted-foreground mt-0.5">Reason: {{ $sus->reason }}</p>
                                <p class="text-[10px] text-muted-foreground font-mono">By Admin: {{ $sus->suspendedBy?->name }}</p>
                            </div>
                            @if($sus->unsuspended_at)
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">UNSUSPENDED</span>
                                    <p class="text-[10px] text-muted-foreground mt-1">{{ $sus->unsuspended_at->format('M d, Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.platform>
