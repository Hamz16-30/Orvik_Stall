@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Payment Channels Report" subtitle="Transaction distribution across payment channels" activeNav="reports">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.reports') }}" class="p-2 rounded-xl bg-white border border-border text-muted-foreground hover:text-foreground">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">Payment Methods & Collections Report</h1>
                    <p class="text-xs text-muted-foreground">Period: <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($summary['methods_breakdown'] as $mb)
                <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-foreground">{{ $mb['label'] }}</span>
                        <span class="text-xs font-bold font-mono text-accent">{{ $mb['percentage'] }}%</span>
                    </div>
                    <p class="text-lg font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($mb['total'], 2) }}</p>
                    <span class="text-[10px] text-muted-foreground font-mono block">{{ number_format($mb['count']) }} transactions</span>
                </div>
            @endforeach
        </div>

        <!-- Table -->
        <x-ui.card>
            <x-ui.table :headers="['Invoice #', 'Date & Time', 'Payment Method', 'Customer', 'Cashier', 'Amount Collected']">
                @forelse($rows as $sale)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            <a href="{{ route('app.sales.show', $sale->id) }}" class="hover:underline hover:text-accent">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                            {{ $sale->completed_at ? $sale->completed_at->format('M d, Y H:i') : '' }}
                        </td>
                        <td class="px-5 py-3.5 font-bold capitalize text-foreground">
                            {{ str_replace('_', ' ', $sale->payment_method) }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-foreground">
                            {{ $sale->customer ? $sale->customer->name : 'Walk-in' }}
                        </td>
                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $sale->user ? $sale->user->name : 'Staff' }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-extrabold text-foreground">
                            {{ $currency }}{{ number_format($sale->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No transactions recorded for the selected filters.
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
