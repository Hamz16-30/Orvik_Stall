@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Inventory Report" subtitle="Stock valuation, threshold compliance, and dead stock audits" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Inventory Valuation & Status Report</h1>
                    <p class="text-xs text-muted-foreground">Comprehensive stock audit across active catalog items</p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.print', array_merge(['type' => 'inventory'], request()->query())) }}"
                    target="_blank"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Print</span>
                </a>
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'inventory'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
                <a
                    href="{{ route('app.reports.export.excel', array_merge(['type' => 'inventory'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Excel</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Active Products</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_products']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Est. Stock Valuation</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_valuation'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-rose-700">Out of Stock</span>
                <p class="text-base font-extrabold font-mono text-rose-700">{{ number_format($summary['out_of_stock']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-amber-700">Low Stock</span>
                <p class="text-base font-extrabold font-mono text-amber-800">{{ number_format($summary['low_stock']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Dead Stock (30d+)</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['dead_stock']) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.inventory') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search product name, SKU, barcode..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <div>
                    <select
                        name="stock_status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Stock Statuses</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="dead_stock" {{ request('stock_status') === 'dead_stock' ? 'selected' : '' }}>Dead Stock (30d+)</option>
                        <option value="never_sold" {{ request('stock_status') === 'never_sold' ? 'selected' : '' }}>Never Sold</option>
                    </select>
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

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.inventory') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Product Name', 'Category', 'Stock on Hand', 'Unit Cost', 'Selling Price', 'Est. Stock Value', 'Status', 'Last Sold', 'Action']">
                @forelse($rows as $p)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            <span>{{ $p->name }}</span>
                            @if($p->sku)
                                <span class="text-[10px] text-muted-foreground font-mono block">{{ $p->sku }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $p->category_name }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ $p->stock_quantity }} {{ $p->unit }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($p->cost_price, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-foreground font-semibold">
                            {{ $currency }}{{ number_format($p->selling_price, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($p->stock_value, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <x-ui.badge :variant="$p->status_variant" size="sm">{{ $p->status }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono text-[11px]">
                            {{ $p->last_sold_date }}
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <a href="{{ route('app.inventory.show', $p->id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Product">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No inventory items found matching your filters.
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
