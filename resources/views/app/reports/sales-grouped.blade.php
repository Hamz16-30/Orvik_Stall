@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="{{ $title }}" subtitle="Aggregated sales performance rollup" activeNav="reports">
    <div class="space-y-6">
        <!-- Top Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $title }}</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.sales') }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary"
                >
                    <i data-lucide="list" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Itemized Transactions</span>
                </a>
            </div>
        </div>

        <x-ui.card>
            <x-ui.table :headers="['Time Interval', 'Orders', 'Gross Sales', 'Discounts', 'Net Sales', 'COGS', 'Gross Profit']">
                @forelse($rows as $row)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-4 font-bold text-foreground">
                            {{ $row['label'] }}
                        </td>
                        <td class="px-5 py-4 font-mono font-semibold text-foreground">
                            {{ $row['orders_count'] }}
                        </td>
                        <td class="px-5 py-4 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($row['gross_sales'], 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono text-emerald-700">
                            {{ $row['discounts'] > 0 ? '-' . $currency . number_format($row['discounts'], 2) : '—' }}
                        </td>
                        <td class="px-5 py-4 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($row['net_sales'], 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono text-muted-foreground">
                            {{ $currency }}{{ number_format($row['cogs'], 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono font-bold text-emerald-700">
                            {{ $currency }}{{ number_format($row['gross_profit'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No sales records found for this period.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
