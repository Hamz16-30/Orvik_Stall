@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Customer Report" subtitle="Customer purchase volume, lifetime revenue, and average basket size" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Customer Performance Report</h1>
                    <p class="text-xs text-muted-foreground">Lifetime customer valuation and purchasing metrics</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'customers'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Total Customer Profiles</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_customers']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-emerald-700">Ordering Customers</span>
                <p class="text-base font-extrabold font-mono text-emerald-700">{{ number_format($summary['active_customers']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Total Customer Revenue</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_customer_revenue'], 2) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.customers') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by customer name, phone, email..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <div>
                    <select
                        name="sort_by"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="spent" {{ $sortBy === 'spent' ? 'selected' : '' }}>Sort by Total Spent</option>
                        <option value="orders" {{ $sortBy === 'orders' ? 'selected' : '' }}>Sort by Orders Count</option>
                        <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Sort by Name</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.customers') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Customer Name', 'Phone', 'Orders Completed', 'Total Spent', 'Average Order Value', 'Last Purchase', 'Action']">
                @forelse($rows as $c)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            <span>{{ $c->name }}</span>
                            @if($c->email)
                                <span class="text-[10px] text-muted-foreground font-normal block">{{ $c->email }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono">
                            {{ $c->phone ?: '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ number_format($c->orders_count) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($c->total_spent, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($c->average_order_value, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono text-[11px]">
                            {{ $c->last_purchase_date }}
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <a href="{{ route('app.customers.show', $c->id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Customer">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No customer records found.
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
