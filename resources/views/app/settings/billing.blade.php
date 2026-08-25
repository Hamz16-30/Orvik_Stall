<x-layouts.app title="Billing & Subscriptions" subtitle="Manage invoices, subscription intervals, and payment status" activeNav="settings">
    <div class="space-y-6">
        <!-- Settings Nav Tabs -->
        <x-navigation.settings-nav active="billing" />

        <!-- Subscription Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Current Subscription -->
            <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-muted-foreground font-semibold uppercase">Current Subscription</span>
                        <h2 class="text-lg font-bold text-foreground">{{ $plan?->name ?? 'Free' }} Tier</h2>
                    </div>

                    @if($subscription && $subscription->isTrialing())
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            14-Day Free Trial
                        </span>
                    @elseif($subscription && $subscription->isActive())
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Active
                        </span>
                    @elseif($subscription && $subscription->isCancelled())
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            Cancelled
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-secondary/50 border border-border">
                        <span class="text-[10px] uppercase font-bold text-muted-foreground">Price</span>
                        <p class="text-base font-extrabold font-mono text-foreground">Rs. {{ number_format($plan?->price ?? 0) }}/mo</p>
                    </div>

                    <div class="p-3 rounded-xl bg-secondary/50 border border-border">
                        <span class="text-[10px] uppercase font-bold text-muted-foreground">Billing Cycle</span>
                        <p class="text-base font-bold text-foreground">{{ ucfirst($subscription?->plan?->billing_interval ?? 'Monthly') }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-secondary/50 border border-border">
                        <span class="text-[10px] uppercase font-bold text-muted-foreground">Next Renewal / Expiry</span>
                        <p class="text-xs font-mono font-bold text-foreground mt-1">
                            {{ $subscription?->ends_at ? $subscription->ends_at->format('M d, Y') : ($subscription?->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : 'Active') }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-border/70">
                    <a href="{{ route('app.settings.plan') }}" class="text-xs font-bold text-accent hover:underline">
                        &larr; View Resource Usage & Quotas
                    </a>

                    <div class="flex items-center gap-2">
                        @if($subscription && $subscription->cancel_at_period_end)
                            <form method="POST" action="{{ route('app.settings.billing.reactivate') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
                                    Keep Subscription (Reactivate)
                                </button>
                            </form>
                        @elseif($subscription && !$subscription->plan?->isFree())
                            <div x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">
                                    Cancel Subscription
                                </button>

                                <div x-show="open" @click.outside="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                                    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-border">
                                        <h3 class="text-base font-bold text-foreground">Cancel Subscription?</h3>
                                        <p class="text-xs text-muted-foreground">
                                            Your plan will remain active until the end of the current billing cycle. 
                                            <strong>All your products, sales history, and customer records will remain 100% safe.</strong>
                                        </p>
                                        <form method="POST" action="{{ route('app.settings.billing.cancel') }}" class="flex items-center justify-end gap-2 pt-2">
                                            @csrf
                                            <button type="button" @click="open = false" class="px-3 py-2 text-xs text-muted-foreground hover:text-foreground font-semibold">Keep Plan</button>
                                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700">Confirm Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Support / FAQ -->
            <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-foreground">Monetization & Billing FAQ</h3>
                <div class="space-y-2 text-xs text-muted-foreground">
                    <p><strong>Safe Downgrades:</strong> If you change to a plan with lower limits, existing records are never deleted.</p>
                    <p><strong>Payment Methods:</strong> Standard bank transfers, card processing, and manual invoicing supported.</p>
                    <p><strong>Questions?</strong> Contact platform billing support anytime.</p>
                </div>
            </div>
        </div>

        <!-- Invoices History -->
        <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-border/70">
                <h3 class="text-sm font-bold text-foreground">Billing Invoices History</h3>
                <p class="text-xs text-muted-foreground">Official invoices generated for subscription renewals</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                        <tr>
                            <th class="px-5 py-3.5">Invoice #</th>
                            <th class="px-5 py-3.5">Issued Date</th>
                            <th class="px-5 py-3.5">Amount</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60 text-foreground">
                        @forelse($invoices as $inv)
                            <tr class="hover:bg-muted/20 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold">{{ $inv->invoice_number }}</td>
                                <td class="px-5 py-3.5 text-muted-foreground">{{ $inv->issued_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 font-mono font-extrabold">{{ $inv->currency }} {{ number_format($inv->amount, 2) }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-muted-foreground">
                                    No billing invoices recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-border/70">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
