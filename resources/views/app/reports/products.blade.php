@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Product Performance Report" subtitle="Volume sold, revenue, and gross profit per product" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Product Performance Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.print', array_merge(['type' => 'products'], request()->query())) }}"
                    target="_blank"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Print</span>
                </a>
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'products'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
                <a
                    href="{{ route('app.reports.export.excel', array_merge(['type' => 'products'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Excel</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Products Sold</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_products_sold']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Units Sold</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_units']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Total Revenue</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_revenue'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Total COGS</span>
                <p class="text-base font-extrabold font-mono text-muted-foreground">{{ $currency }}{{ number_format($summary['total_cogs'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Gross Profit</span>
                <p class="text-base font-extrabold font-mono text-emerald-700">{{ $currency }}{{ number_format($summary['total_profit'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Average Margin</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ $summary['average_margin'] }}%</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.products') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by product name or SKU..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <div>
                    <select
                        name="category_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select
                        name="sort_by"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="revenue" {{ $sortBy === 'revenue' ? 'selected' : '' }}>Sort by Revenue</option>
                        <option value="units" {{ $sortBy === 'units' ? 'selected' : '' }}>Sort by Units Sold</option>
                        <option value="profit" {{ $sortBy === 'profit' ? 'selected' : '' }}>Sort by Gross Profit</option>
                        <option value="margin" {{ $sortBy === 'margin' ? 'selected' : '' }}>Sort by Margin %</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.products') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Product Name', 'Category', 'Units Sold', 'Revenue', 'COGS', 'Gross Profit', 'Margin %', 'Action']">
                @forelse($rows as $p)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            <span>{{ $p->product_name }}</span>
                            @if($p->sku)
                                <span class="text-[10px] text-muted-foreground font-mono block">{{ $p->sku }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $p->category_name }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ number_format($p->units_sold) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($p->revenue, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($p->cogs, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-emerald-700">
                            {{ $currency }}{{ number_format($p->gross_profit, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-semibold text-foreground">
                            {{ $p->margin }}%
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            @if($p->product_id)
                                <a href="{{ route('app.inventory.show', $p->product_id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Product">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No product sales recorded for the selected filters.
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
