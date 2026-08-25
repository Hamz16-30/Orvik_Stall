@props([
    'items' => [
        ['name' => 'Organic Matcha Powder 50g', 'sku' => 'OMP-02', 'current' => 2, 'min' => 10, 'status' => 'Critical'],
        ['name' => 'Cold Brew Glass Bottle', 'sku' => 'CBG-18', 'current' => 4, 'min' => 15, 'status' => 'Low'],
    ]
])

<div class="bg-white p-5 rounded-xl border border-border/80 shadow-2xs">
    <div class="flex items-center justify-between pb-3 border-b border-border/50">
        <div>
            <h4 class="text-sm font-semibold text-foreground">Inventory Watch</h4>
            <p class="text-[11px] text-muted-foreground">Items nearing reorder threshold</p>
        </div>
        <x-ui.badge variant="low-stock" size="sm">2 Low</x-ui.badge>
    </div>

    <div class="mt-3 divide-y divide-border/50">
        @foreach($items as $item)
            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                <div class="min-w-0">
                    <p class="font-semibold text-foreground truncate">{{ $item['name'] }}</p>
                    <p class="text-[10px] text-muted-foreground">{{ $item['sku'] }} &bull; Min req: {{ $item['min'] }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                        {{ $item['current'] }} left
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
