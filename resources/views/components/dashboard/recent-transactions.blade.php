@props([
    'transactions' => [
        ['id' => '#ORD-4921', 'items' => '2 items &bull; Cash', 'amount' => '$42.00', 'time' => '2 min ago', 'status' => 'Completed'],
        ['id' => '#ORD-4920', 'items' => '1 item &bull; Card', 'amount' => '$128.50', 'time' => '14 min ago', 'status' => 'Completed'],
        ['id' => '#ORD-4919', 'items' => '4 items &bull; QR Pay', 'amount' => '$65.00', 'time' => '28 min ago', 'status' => 'Completed'],
    ]
])

<div class="bg-white p-5 rounded-xl border border-border/80 shadow-2xs">
    <div class="flex items-center justify-between pb-3 border-b border-border/50">
        <div>
            <h4 class="text-sm font-semibold text-foreground">Recent Stall Transactions</h4>
            <p class="text-[11px] text-muted-foreground">Real-time point-of-sale stream</p>
        </div>
        <a href="{{ route('app.sales') }}" class="text-xs font-semibold text-accent hover:underline flex items-center gap-1">
            <span>View All</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <div class="mt-3 divide-y divide-border/50">
        @foreach($transactions as $tx)
            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100/70 text-emerald-700 flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground">{{ $tx['id'] }}</p>
                        <p class="text-[10px] text-muted-foreground">{!! $tx['items'] !!} &bull; {{ $tx['time'] }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-foreground">{{ $tx['amount'] }}</p>
                    <span class="text-[10px] font-medium text-emerald-600">Paid</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
