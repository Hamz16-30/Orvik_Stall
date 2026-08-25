@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="{{ $supplier->name }}" subtitle="Supplier profile and purchase ledger" activeNav="suppliers">
    <div class="max-w-4xl space-y-6">
        <!-- Back Navigation & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.suppliers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Suppliers</span>
            </a>

            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="edit-3" :href="route('app.suppliers.edit', $supplier->id)">
                    Edit Profile
                </x-ui.button>
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" :href="route('app.purchases.create')">
                    Record Purchase
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Supplier Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-dashboard.metric-card
                label="Total Purchases"
                value="{{ $supplier->total_purchases }} purchase(s)"
                change="Procurement Orders"
                trend="neutral"
                icon="package"
            />
            <x-dashboard.metric-card
                label="Total Procurement Spend"
                value="{{ $currency }}{{ number_format($supplier->total_spent, 2) }}"
                change="Cumulative Outflow"
                trend="neutral"
                icon="dollar-sign"
            />
            <x-dashboard.metric-card
                label="Last Purchase Date"
                value="{{ $supplier->last_purchase_date ?? 'No purchases yet' }}"
                change="Recent Order"
                trend="neutral"
                icon="calendar"
            />
        </div>

        <!-- Supplier Details Box -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border">
                <div>
                    <h3 class="text-base font-bold text-foreground">{{ $supplier->name }}</h3>
                    <p class="text-xs text-muted-foreground">{{ $supplier->company_name ?: 'Independent Vendor' }}</p>
                </div>
                @if($supplier->status === 'active')
                    <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                @else
                    <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-muted-foreground block text-[11px]">Phone:</span>
                    <span class="font-bold text-foreground font-mono">{{ $supplier->phone ?: 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[11px]">Email:</span>
                    <span class="font-bold text-foreground">{{ $supplier->email ?: 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block text-[11px]">Warehouse Address:</span>
                    <span class="font-bold text-foreground">{{ $supplier->address ?: 'Not provided' }}</span>
                </div>
            </div>

            @if($supplier->notes)
                <div class="p-3 bg-secondary/30 rounded-xl text-xs">
                    <span class="text-[11px] font-semibold text-muted-foreground block mb-0.5">Notes:</span>
                    <p class="text-foreground">{{ $supplier->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Supplier Purchases Table -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-foreground">Purchase History</h3>
            <x-ui.card>
                <x-ui.table :headers="['Purchase #', 'Date', 'Items', 'Total Amount', 'Payment', 'Status', 'Voucher']">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                                <a href="{{ route('app.purchases.show', $purchase->id) }}" class="hover:text-accent hover:underline">
                                    {{ $purchase->purchase_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground font-mono">
                                {{ $purchase->purchased_at ? $purchase->purchased_at->format('M d, Y') : '' }}
                            </td>
                            <td class="px-5 py-3.5 text-foreground">
                                {{ $purchase->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                                {{ $currency }}{{ number_format($purchase->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 capitalize">
                                {{ str_replace('_', ' ', $purchase->payment_method) }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($purchase->status === 'completed')
                                    <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('app.purchases.receipt', $purchase->id) }}" target="_blank" class="p-1 text-muted-foreground hover:text-accent">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-xs text-muted-foreground">
                                No purchases recorded with this supplier yet.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if($purchases->hasPages())
                    <div class="px-5 py-4 border-t border-border">
                        {{ $purchases->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
