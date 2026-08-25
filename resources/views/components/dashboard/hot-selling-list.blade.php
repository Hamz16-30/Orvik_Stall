@props([
    'items' => [
        ['name' => 'Artisan Leather Wallet', 'sku' => 'ALW-01', 'sold' => 48, 'revenue' => '$1,680.00', 'tag' => 'Top 1'],
        ['name' => 'Ceramic Brew Mug', 'sku' => 'CBM-09', 'sold' => 36, 'revenue' => '$864.00', 'tag' => 'Top 2'],
        ['name' => 'Linen Canvas Tote', 'sku' => 'LCT-14', 'sold' => 29, 'revenue' => '$725.00', 'tag' => 'Top 3'],
    ]
])

<div class="bg-white p-5 rounded-xl border border-border/80 shadow-2xs">
    <div class="flex items-center justify-between pb-3 border-b border-border/50">
        <div>
            <h4 class="text-sm font-semibold text-foreground">Hot Selling Items</h4>
            <p class="text-[11px] text-muted-foreground">Fastest velocity this week</p>
        </div>
        <x-ui.badge variant="accent" size="sm" :dot="false">High Demand</x-ui.badge>
    </div>

    <div class="mt-3 divide-y divide-border/50">
        @foreach($items as $item)
            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-5 h-5 rounded-md bg-secondary flex items-center justify-center font-bold text-[10px] text-foreground shrink-0">
                        {{ $loop->iteration }}
                    </span>
                    <div class="truncate">
                        <p class="font-semibold text-foreground truncate">{{ $item['name'] }}</p>
                        <p class="text-[10px] text-muted-foreground">{{ $item['sku'] }} &bull; {{ $item['sold'] }} sold</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-foreground">{{ $item['revenue'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
