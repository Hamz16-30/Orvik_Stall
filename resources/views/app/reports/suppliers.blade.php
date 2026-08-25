@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Supplier Report" subtitle="Procurement volume, vendor balances, and delivery frequency" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Supplier Performance Report</h1>
                    <p class="text-xs text-muted-foreground">Vendor procurement audit and payment status breakdown</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'suppliers'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Registered Suppliers</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['total_suppliers']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Total Supplier Spending</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_procurement_value'], 2) }}</p>
            </div>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Supplier / Company', 'Contact Phone', 'Purchases Completed', 'Total Procurement', 'Paid Amount', 'Unpaid Balance', 'Last Purchase', 'Action']">
                @forelse($rows as $s)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            <span>{{ $s->name }}</span>
                            @if($s->company_name)
                                <span class="text-[10px] text-muted-foreground font-normal block">{{ $s->company_name }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono">
                            {{ $s->phone ?: '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ number_format($s->purchases_count) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($s->total_purchases_value, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-emerald-700">
                            {{ $currency }}{{ number_format($s->paid_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-rose-700 font-semibold">
                            {{ $s->unpaid_amount > 0 ? $currency . number_format($s->unpaid_amount, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono text-[11px]">
                            {{ $s->last_purchase_date }}
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <a href="{{ route('app.suppliers.show', $s->id) }}" class="p-1 text-muted-foreground hover:text-accent" title="View Supplier">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No supplier performance records found.
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
