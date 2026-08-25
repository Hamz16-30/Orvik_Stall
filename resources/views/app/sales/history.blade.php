@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Sales History" subtitle="Chronological transactions, receipts, and revenue records" activeNav="sales">
    <div class="space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">Sales Transactions</h2>
                <p class="text-xs text-muted-foreground">Review completed stall orders, customer receipts, and operational audit trail</p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="shopping-cart" :href="route('app.sales')">
                    Open POS Register
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-dashboard.metric-card
                label="Today's Total Sales"
                value="{{ $currency }}{{ number_format($dailySummary['today_revenue'], 2) }}"
                change="Daily Revenue"
                trend="up"
                icon="dollar-sign"
            />

            <x-dashboard.metric-card
                label="Today's Orders"
                value="{{ $dailySummary['today_orders'] }} Order(s)"
                change="Completed Transactions"
                trend="neutral"
                icon="shopping-bag"
            />

            <x-dashboard.metric-card
                label="Average Order Value"
                value="{{ $currency }}{{ number_format($dailySummary['average_order'], 2) }}"
                change="Per Sale Average"
                trend="neutral"
                icon="bar-chart-2"
            />
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.sales.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by invoice # or product name..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                </div>

                <!-- Date Range Filter -->
                <div>
                    <select
                        name="date_range"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ request('date_range', 'today') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div>
                    <select
                        name="payment_method"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Payment Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="mobile_payment" {{ request('payment_method') === 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <!-- Status Filter & Clear -->
                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-1/2 px-2.5 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>

                    <button type="submit" class="px-3 py-2 text-xs font-semibold rounded-lg bg-secondary text-foreground hover:bg-muted transition-colors shrink-0">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'date_range', 'payment_method', 'status']))
                        <a href="{{ route('app.sales.history') }}" class="p-2 text-muted-foreground hover:text-foreground text-xs" title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Desktop Sales Table -->
        <div class="hidden md:block">
            <x-ui.card>
                <x-ui.table :headers="['Invoice #', 'Date & Time', 'Cashier', 'Items', 'Total Amount', 'Payment', 'Status', 'Actions']">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <!-- Invoice Number -->
                            <td class="px-5 py-4 font-mono font-bold text-foreground">
                                <a href="{{ route('app.sales.show', $sale->id) }}" class="hover:text-accent hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>

                            <!-- Completed Date -->
                            <td class="px-5 py-4 text-muted-foreground font-mono whitespace-nowrap">
                                {{ $sale->completed_at ? $sale->completed_at->format('M d, Y H:i') : 'N/A' }}
                            </td>

                            <!-- Cashier -->
                            <td class="px-5 py-4 font-semibold text-foreground">
                                {{ $sale->user ? $sale->user->name : 'N/A' }}
                            </td>

                            <!-- Items Summary -->
                            <td class="px-5 py-4 text-muted-foreground">
                                <span class="font-bold text-foreground">{{ $sale->items->count() }} item(s)</span>
                                <span class="text-[11px] block truncate max-w-xs text-muted-foreground/80">
                                    {{ $sale->items->pluck('product_name')->take(2)->join(', ') }}{{ $sale->items->count() > 2 ? '...' : '' }}
                                </span>
                            </td>

                            <!-- Total Amount -->
                            <td class="px-5 py-4 font-extrabold text-foreground font-mono">
                                {{ $currency }}{{ number_format($sale->total_amount, 2) }}
                                @if($sale->discount_amount > 0)
                                    <span class="block text-[10px] text-emerald-600 font-normal">Discount: -{{ $currency }}{{ number_format($sale->discount_amount, 2) }}</span>
                                @endif
                            </td>

                            <!-- Payment Method -->
                            <td class="px-5 py-4">
                                <span class="capitalize px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary text-foreground">
                                    {{ str_replace('_', ' ', $sale->payment_method) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                @if($sale->status === 'completed')
                                    <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                                @elseif($sale->status === 'voided')
                                    <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary" size="sm">{{ ucfirst($sale->status) }}</x-ui.badge>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('app.sales.show', $sale->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="View Sale Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('app.sales.receipt', $sale->id) }}" target="_blank" class="p-1 rounded text-muted-foreground hover:text-accent" title="Print Receipt">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                                No sales transactions found for the selected period.
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

        <!-- Mobile Sales Card List -->
        <div class="md:hidden space-y-3">
            @forelse($sales as $sale)
                <div class="bg-white p-4 rounded-xl border border-border/80 shadow-2xs space-y-2.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('app.sales.show', $sale->id) }}" class="font-bold text-foreground font-mono text-sm hover:text-accent">
                                {{ $sale->invoice_number }}
                            </a>
                            <p class="text-[11px] text-muted-foreground">{{ $sale->completed_at ? $sale->completed_at->format('M d, Y H:i') : '' }}</p>
                        </div>
                        @if($sale->status === 'completed')
                            <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                        @else
                            <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-border/40">
                        <span class="text-muted-foreground">Total:</span>
                        <span class="font-extrabold text-foreground font-mono text-sm">{{ $currency }}{{ number_format($sale->total_amount, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-border/40 text-xs">
                        <a href="{{ route('app.sales.receipt', $sale->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-border bg-secondary font-semibold">Receipt</a>
                        <a href="{{ route('app.sales.show', $sale->id) }}" class="px-3 py-1.5 rounded-lg bg-accent text-white font-semibold">Details</a>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-border text-center text-xs text-muted-foreground">
                    No sales records found.
                </div>
            @endforelse

            @if($sales->hasPages())
                <div class="pt-2">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
