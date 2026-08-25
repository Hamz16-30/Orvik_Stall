@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Stock Movement Audit Trail" subtitle="Chronological history of all stock changes" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Stock Movement Audit Trail</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports.inventory') }}" class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary">
                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Inventory Status</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Total Stock Movements</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_movements']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-emerald-700">Total Stock Ingested</span>
                <p class="text-base font-extrabold font-mono text-emerald-700">+{{ number_format($summary['total_stock_in']) }} units</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-rose-700">Total Stock Outflow</span>
                <p class="text-base font-extrabold font-mono text-rose-700">-{{ number_format($summary['total_stock_out']) }} units</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.reports.inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search product, reference, notes..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    />
                </div>

                <div>
                    <select
                        name="movement_type"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Movement Types</option>
                        <option value="SALE" {{ request('movement_type') === 'SALE' ? 'selected' : '' }}>SALE</option>
                        <option value="PURCHASE" {{ request('movement_type') === 'PURCHASE' ? 'selected' : '' }}>PURCHASE</option>
                        <option value="SALE_RETURN" {{ request('movement_type') === 'SALE_RETURN' ? 'selected' : '' }}>SALE RETURN / VOID</option>
                        <option value="PURCHASE_VOID" {{ request('movement_type') === 'PURCHASE_VOID' ? 'selected' : '' }}>PURCHASE VOID</option>
                        <option value="ADJUSTMENT_ADD" {{ request('movement_type') === 'ADJUSTMENT_ADD' ? 'selected' : '' }}>ADJUSTMENT (+)</option>
                        <option value="ADJUSTMENT_REMOVE" {{ request('movement_type') === 'ADJUSTMENT_REMOVE' ? 'selected' : '' }}>ADJUSTMENT (-)</option>
                    </select>
                </div>

                <div>
                    <select
                        name="product_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >
                        <option value="all">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-secondary text-foreground hover:bg-muted font-bold text-xs rounded-xl">
                        Filter
                    </button>
                    <a href="{{ route('app.reports.inventory.movements') }}" class="py-2 px-3 text-xs text-muted-foreground hover:text-foreground font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Date & Time', 'Product Name', 'Movement Type', 'Quantity', 'Prev Stock', 'New Stock', 'Reference / Operator']">
                @forelse($rows as $m)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                            {{ $m->created_at ? $m->created_at->format('M d, Y H:i') : '' }}
                        </td>
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            {{ $m->product ? $m->product->name : 'Deleted Product' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ str_contains($m->type, 'PURCHASE') || str_contains($m->type, 'ADD') ? 'bg-emerald-100 text-emerald-800' : 'bg-secondary text-foreground' }}">
                                {{ $m->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold {{ $m->quantity > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $m->quantity > 0 ? '+' : '' }}{{ (float)$m->quantity }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ (float)$m->previous_quantity }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ (float)$m->new_quantity }}
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground text-[11px]">
                            <span>{{ $m->reference ?: '—' }}</span>
                            @if($m->user)
                                <span class="block text-[10px] text-muted-foreground/80 font-mono">By: {{ $m->user->name }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No stock movement records found for this period.
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
