@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Sales Report" subtitle="Itemized sales transactions and revenue ledger" activeNav="reports">
    <div x-data="{ filterModal: false, customRangeModal: false }" class="space-y-6">
        <!-- Top Navigation & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Sales Transactions Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.print', array_merge(['type' => 'sales'], request()->query())) }}"
                    target="_blank"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Print</span>
                </a>
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'sales'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
                <a
                    href="{{ route('app.reports.export.excel', array_merge(['type' => 'sales'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Excel</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards Row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Gross Sales</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($summary['gross_sales'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Discounts</span>
                <p class="text-base font-extrabold font-mono text-emerald-700">-{{ $currency }}{{ number_format($summary['discounts'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Taxes</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($summary['taxes'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Net Sales</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['net_sales'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Total Orders</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['orders_count']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Avg Order Value</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($summary['average_order_value'], 2) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by invoice # or customer..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <!-- Date Range Selector -->
                <div>
                    <select
                        name="period"
                        onchange="if(this.value==='custom'){ customRangeModal = true; } else { this.form.submit(); }"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <select
                        name="payment_method"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Payment Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="mobile_payment" {{ request('payment_method') === 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <!-- Cashier / User -->
                <div>
                    <select
                        name="user_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Staff</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit / Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.sales') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Sales Data Table -->
        <x-ui.card>
            <x-ui.table :headers="['Invoice #', 'Date & Time', 'Customer', 'Items', 'Subtotal', 'Discount', 'Total', 'Payment', 'Status', 'Actions']">
                @forelse($rows as $sale)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            <a href="{{ route('app.sales.show', $sale->id) }}" class="hover:text-accent hover:underline">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                            {{ $sale->completed_at ? $sale->completed_at->format('M d, Y H:i') : '' }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-foreground">
                            @if($sale->customer)
                                <a href="{{ route('app.customers.show', $sale->customer->id) }}" class="hover:underline hover:text-accent">
                                    {{ $sale->customer->name }}
                                </a>
                            @else
                                <span class="text-muted-foreground font-normal">Walk-in</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $sale->items->count() }} item(s)
                        </td>
                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($sale->subtotal, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-emerald-700">
                            {{ $sale->discount_amount > 0 ? '-' . $currency . number_format($sale->discount_amount, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 capitalize text-foreground">
                            {{ str_replace('_', ' ', $sale->payment_method) }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($sale->status === 'completed')
                                <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                            @else
                                <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('app.sales.show', $sale->id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Sale">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('app.sales.receipt', $sale->id) }}" target="_blank" class="p-1 text-muted-foreground hover:text-accent" title="Receipt">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No sales records found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>

            @if($rows->hasPages())
                <div class="px-5 py-4 border-t border-border">
                    {{ $rows->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
