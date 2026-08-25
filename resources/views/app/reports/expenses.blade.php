@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Expenses Report" subtitle="Overhead costs, store expenses, and category distributions" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Operational Expenses Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('app.reports.export.csv', array_merge(['type' => 'expenses'], request()->query())) }}"
                    class="px-3 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shadow-2xs"
                >
                    <i data-lucide="download" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>CSV</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1 bg-accent/5">
                <span class="text-[11px] font-semibold text-accent">Total Expenses</span>
                <p class="text-base font-extrabold font-mono text-accent">{{ $currency }}{{ number_format($summary['total_expenses'], 2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Recorded Expenses</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ number_format($summary['expense_count']) }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-1">
                <span class="text-[11px] font-semibold text-muted-foreground">Average Expense</span>
                <p class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($summary['average_expense'], 2) }}</p>
            </div>
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Expense Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Reference', 'Recorded By', 'Status']">
                @forelse($rows as $e)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                            {{ $e->expense_date ? $e->expense_date->format('M d, Y') : '' }}
                        </td>
                        <td class="px-5 py-3.5 font-bold text-foreground">
                            {{ $e->category ? $e->category->name : 'Uncategorized' }}
                        </td>
                        <td class="px-5 py-3.5 text-foreground">
                            {{ $e->description }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($e->amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 capitalize text-muted-foreground">
                            {{ str_replace('_', ' ', $e->payment_method) }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-muted-foreground">
                            {{ $e->reference ?: '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground text-[11px]">
                            {{ $e->user ? $e->user->name : 'System' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($e->status === 'active')
                                <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                            @else
                                <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No expense records found for this period.
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
