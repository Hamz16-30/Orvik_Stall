@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Profitability Report" subtitle="Gross profit, margins, and estimated operating profit" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Executive Profitability & Margins</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.print', array_merge(['type' => 'profit'], request()->query())) }}"
                    target="_blank"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Print</span>
                </a>
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'profit'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
                <a
                    href="{{ route('app.reports.export.excel', array_merge(['type' => 'profit'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Excel</span>
                </a>
            </div>
        </div>

        <!-- Profit Calculation Formula Card -->
        <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Operating Profit Waterfall</span>
                <span class="text-[10px] text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                    Estimated Operating Profit (Non-GAAP)
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="p-3 bg-secondary/50 rounded-xl space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">1. Total Revenue</span>
                    <p class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($kpis['revenue'], 2) }}</p>
                </div>
                <div class="p-3 bg-secondary/50 rounded-xl space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">2. COGS (Cost of Goods)</span>
                    <p class="text-base font-extrabold font-mono text-muted-foreground">-{{ $currency }}{{ number_format($kpis['revenue'] - $kpis['gross_profit'], 2) }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl space-y-1 border border-emerald-200/50">
                    <span class="text-[11px] font-semibold text-emerald-800">3. Gross Profit</span>
                    <p class="text-base font-extrabold font-mono text-emerald-700">{{ $currency }}{{ number_format($kpis['gross_profit'], 2) }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl space-y-1 border border-emerald-200/50">
                    <span class="text-[11px] font-semibold text-emerald-800">Gross Margin %</span>
                    <p class="text-base font-extrabold font-mono text-emerald-700">{{ $kpis['gross_margin'] }}%</p>
                </div>
                <div class="p-3 bg-secondary/50 rounded-xl space-y-1">
                    <span class="text-[11px] font-semibold text-muted-foreground">4. Operating Expenses</span>
                    <p class="text-base font-extrabold font-mono text-rose-700">-{{ $currency }}{{ number_format($kpis['expenses'], 2) }}</p>
                </div>
                <div class="p-3 bg-accent/10 rounded-xl space-y-1 border border-accent/20">
                    <span class="text-[11px] font-semibold text-accent">5. Net Operating Profit</span>
                    <p class="text-base font-extrabold font-mono {{ $kpis['operating_profit'] >= 0 ? 'text-accent' : 'text-rose-700' }}">
                        {{ $currency }}{{ number_format($kpis['operating_profit'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Breakdown Timeline Table -->
        <x-ui.card>
            <x-ui.table :headers="['Date', 'Revenue', 'Cost of Goods (COGS)', 'Gross Profit', 'Expenses', 'Est. Operating Profit']">
                @forelse($breakdown as $row)
                    @if($row['revenue'] > 0 || $row['expenses'] > 0)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-4 font-bold text-foreground">
                                {{ $row['date'] }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-foreground">
                                {{ $currency }}{{ number_format($row['revenue'], 2) }}
                            </td>
                            <td class="px-5 py-4 font-mono text-muted-foreground">
                                {{ $currency }}{{ number_format($row['cogs'], 2) }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-emerald-700">
                                {{ $currency }}{{ number_format($row['gross_profit'], 2) }}
                            </td>
                            <td class="px-5 py-4 font-mono text-rose-700">
                                {{ $row['expenses'] > 0 ? '-' . $currency . number_format($row['expenses'], 2) : '—' }}
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold {{ $row['operating_profit'] >= 0 ? 'text-accent' : 'text-rose-700' }}">
                                {{ $currency }}{{ number_format($row['operating_profit'], 2) }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No financial activity recorded in this date range.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
