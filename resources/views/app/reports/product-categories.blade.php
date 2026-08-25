@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Category Performance Report" subtitle="Sales volume and gross profit per product category" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Category Performance Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports.products') }}" class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary">
                    <i data-lucide="package" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Itemized Products</span>
                </a>
            </div>
        </div>

        <x-ui.card>
            <x-ui.table :headers="['Category Name', 'Products Active', 'Units Sold', 'Revenue', 'COGS', 'Gross Profit', 'Margin %']">
                @forelse($rows as $cat)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-4 font-bold text-foreground">
                            {{ $cat->category_name }}
                        </td>
                        <td class="px-5 py-4 font-mono font-semibold text-foreground">
                            {{ $cat->product_count }} item(s)
                        </td>
                        <td class="px-5 py-4 font-mono font-bold text-foreground">
                            {{ number_format($cat->units_sold) }}
                        </td>
                        <td class="px-5 py-4 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($cat->revenue, 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($cat->cogs, 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono font-bold text-emerald-700">
                            {{ $currency }}{{ number_format($cat->gross_profit, 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono font-semibold text-foreground">
                            {{ $cat->margin }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No category sales recorded for this period.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
