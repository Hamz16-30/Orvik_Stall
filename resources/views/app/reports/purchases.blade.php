@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Purchases Report" subtitle="Procurement invoices, supplier bills, and fulfillment costs" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Purchases & Procurement Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'purchases'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Total Procurement</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_purchases_value'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Purchase Invoices</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['purchase_count']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-emerald-700">Paid Invoices</span>
                <p class="text-base font-extrabold font-mono text-emerald-700">{{ $currency }}{{ number_format($summary['paid_purchases_value'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-rose-700">Unpaid / Outstanding</span>
                <p class="text-base font-extrabold font-mono text-rose-700">{{ $currency }}{{ number_format($summary['unpaid_purchases_value'], 2) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.purchases') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by PO # or supplier..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <div>
                    <select
                        name="supplier_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select
                        name="payment_status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Payment Statuses</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.purchases') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['PO #', 'Date', 'Supplier', 'Items', 'Total Cost', 'Payment Status', 'Status', 'Actions']">
                @forelse($rows as $p)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            <a href="{{ route('app.purchases.show', $p->id) }}" class="hover:text-accent hover:underline">
                                {{ $p->purchase_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                            {{ $p->purchased_at ? $p->purchased_at->format('M d, Y') : '' }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-foreground">
                            @if($p->supplier)
                                <a href="{{ route('app.suppliers.show', $p->supplier->id) }}" class="hover:underline hover:text-accent">
                                    {{ $p->supplier->name }}
                                </a>
                            @else
                                <span class="text-muted-foreground font-normal">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $p->items->count() }} item(s)
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($p->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 capitalize">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $p->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $p->payment_status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($p->status === 'completed')
                                <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                            @else
                                <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <a href="{{ route('app.purchases.show', $p->id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Purchase">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No purchases recorded for the selected filters.
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
