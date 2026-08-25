@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="{{ $customer->name }}" subtitle="Customer profile and transaction ledger" activeNav="customers">
    <div class="max-w-4xl space-y-6">
        <!-- Back Navigation & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Directory</span>
            </a>

            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="edit-3" :href="route('app.customers.edit', $customer->id)">
                    Edit Profile
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Customer Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-dashboard.metric-card
                label="Total Orders"
                value="{{ $customer->total_orders }} order(s)"
                change="Lifetime Purchases"
                trend="neutral"
                icon="shopping-bag"
            />
            <x-dashboard.metric-card
                label="Total Lifetime Spend"
                value="{{ $currency }}{{ number_format($customer->total_spent, 2) }}"
                change="Cumulative Revenue"
                trend="up"
                icon="dollar-sign"
            />
            <x-dashboard.metric-card
                label="Last Purchase"
                value="{{ $customer->last_purchase_date ?? 'No purchases yet' }}"
                change="Recent Activity"
                trend="neutral"
                icon="calendar"
            />
        </div>

        <!-- Customer Contact Details -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border">
                <h3 class="text-base font-bold text-foreground">{{ $customer->name }}</h3>
                @if($customer->status === 'active')
                    <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                @else
                    <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-muted-foreground block text-[11px]">Phone:</span>
                    <span class="font-bold text-foreground font-mono">{{ $customer->phone ?: 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[11px]">Email:</span>
                    <span class="font-bold text-foreground">{{ $customer->email ?: 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[11px]">Address:</span>
                    <span class="font-bold text-foreground">{{ $customer->address ?: 'Not provided' }}</span>
                </div>
            </div>

            @if($customer->notes)
                <div class="p-3 bg-secondary/30 rounded-xl text-xs">
                    <span class="text-[11px] font-semibold text-muted-foreground block mb-0.5">Notes:</span>
                    <p class="text-foreground">{{ $customer->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Customer Sales History -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-foreground">Purchase History</h3>
            <x-ui.card>
                <x-ui.table :headers="['Invoice #', 'Date', 'Items', 'Total', 'Payment', 'Status', 'Receipt']">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                                <a href="{{ route('app.sales.show', $sale->id) }}" class="hover:text-accent hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground font-mono">
                                {{ $sale->completed_at ? $sale->completed_at->format('M d, Y H:i') : '' }}
                            </td>
                            <td class="px-5 py-3.5 text-foreground">
                                {{ $sale->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                                {{ $currency }}{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 capitalize">
                                {{ str_replace('_', ' ', $sale->payment_method) }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($sale->status === 'completed')
                                    <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('app.sales.receipt', $sale->id) }}" target="_blank" class="p-1 text-muted-foreground hover:text-accent">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-xs text-muted-foreground">
                                No purchase transactions recorded for this customer yet.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if($sales->hasPages())
                    <div class="px-5 py-4 border-t border-border">
                        {{ $sales->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
