@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Purchases" subtitle="Procurement invoices and incoming stock inventory logs" activeNav="purchases">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">Purchase Records</h2>
                <p class="text-xs text-muted-foreground">Log supplier stock shipments, cost tracking, and inventory intake</p>
            </div>

            <div class="flex items-center gap-2.5">
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

        <!-- Summary Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-dashboard.metric-card
                label="Today's Purchases"
                value="{{ $currency }}{{ number_format($summary['today_total'], 2) }}"
                change="{{ $summary['today_count'] }} order(s) today"
                trend="neutral"
                icon="shopping-bag"
            />
            <x-dashboard.metric-card
                label="This Month's Spending"
                value="{{ $currency }}{{ number_format($summary['month_total'], 2) }}"
                change="Monthly Stock Procurement"
                trend="neutral"
                icon="dollar-sign"
            />
            <x-dashboard.metric-card
                label="Active Suppliers"
                value="{{ $suppliers->count() }} vendor(s)"
                change="Procurement Network"
                trend="up"
                icon="truck"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs">
            <form method="GET" action="{{ route('app.purchases.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by purchase # or supplier..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                </div>

                <div>
                    <select
                        name="supplier_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select
                        name="date_range"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>

                    <button type="submit" class="px-3 py-2 text-xs font-bold rounded-lg bg-secondary text-foreground hover:bg-muted shrink-0">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Desktop Purchases Table -->
        <div class="hidden md:block">
            <x-ui.card>
                <x-ui.table :headers="['Purchase #', 'Date', 'Supplier', 'Items', 'Total Cost', 'Payment', 'Status', 'Actions']">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-4 font-mono font-bold text-foreground">
                                <a href="{{ route('app.purchases.show', $purchase->id) }}" class="hover:text-accent hover:underline">
                                    {{ $purchase->purchase_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground font-mono">
                                {{ $purchase->purchased_at ? $purchase->purchased_at->format('M d, Y') : '' }}
                            </td>
                            <td class="px-5 py-4 font-bold text-foreground">
                                {{ $purchase->supplier ? $purchase->supplier->name : 'Unassigned Vendor' }}
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ $purchase->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-foreground">
                                {{ $currency }}{{ number_format($purchase->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-4 capitalize">
                                {{ str_replace('_', ' ', $purchase->payment_method) }}
                            </td>
                            <td class="px-5 py-4">
                                @if($purchase->status === 'completed')
                                    <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('app.purchases.show', $purchase->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('app.purchases.receipt', $purchase->id) }}" target="_blank" class="p-1 rounded text-muted-foreground hover:text-accent" title="Print Voucher">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                                No purchases found. <a href="{{ route('app.purchases.create') }}" class="text-accent underline font-semibold">Record a purchase</a>
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

        <!-- Mobile Purchases List -->
        <div class="md:hidden space-y-3">
            @forelse($purchases as $purchase)
                <div class="bg-white p-4 rounded-xl border border-border/80 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('app.purchases.show', $purchase->id) }}" class="font-bold text-foreground font-mono text-sm hover:text-accent">
                                {{ $purchase->purchase_number }}
                            </a>
                            <p class="text-[11px] text-muted-foreground">{{ $purchase->supplier ? $purchase->supplier->name : 'Unassigned' }} &bull; {{ $purchase->purchased_at ? $purchase->purchased_at->format('M d') : '' }}</p>
                        </div>
                        @if($purchase->status === 'completed')
                            <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                        @else
                            <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex justify-between pt-1 border-t border-border/40">
                        <span class="text-muted-foreground">Total Cost:</span>
                        <span class="font-mono font-bold text-foreground">{{ $currency }}{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-border/40">
                        <a href="{{ route('app.purchases.receipt', $purchase->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-border bg-secondary font-semibold">Voucher</a>
                        <a href="{{ route('app.purchases.show', $purchase->id) }}" class="px-3 py-1.5 rounded-lg bg-accent text-white font-semibold">Details</a>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-border text-center text-xs text-muted-foreground">
                    No purchase records found.
                </div>
            @endforelse

            @if($purchases->hasPages())
                <div class="pt-2">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
