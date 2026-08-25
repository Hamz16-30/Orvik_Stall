@props([
    'items' => [
        ['name' => 'Vintage Brass Keyring', 'sku' => 'VBK-44', 'daysIdle' => 64, 'stock' => 18, 'costLocked' => '$270.00'],
        ['name' => 'Wool Felt Coasters (Set)', 'sku' => 'WFC-12', 'daysIdle' => 52, 'stock' => 24, 'costLocked' => '$360.00'],
    ]
])

<div class="bg-white p-5 rounded-xl border border-border/80 shadow-2xs">
    <div class="flex items-center justify-between pb-3 border-b border-border/50">
        <div>
            <h4 class="text-sm font-semibold text-foreground">Dead Stock Alerts</h4>
            <p class="text-[11px] text-muted-foreground">Unsold > 45 days (Capital locked)</p>
        </div>
        <x-ui.badge variant="warning" size="sm">Action Needed</x-ui.badge>
    </div>

    <div class="mt-3 divide-y divide-border/50">
        @foreach($items as $item)
            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                <div class="min-w-0">
                    <p class="font-semibold text-foreground truncate">{{ $item['name'] }}</p>
                    <p class="text-[10px] text-rose-600 font-medium">{{ $item['daysIdle'] }} days idle &bull; {{ $item['stock'] }} in stock</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-foreground">{{ $item['costLocked'] }}</p>
                    <span class="text-[10px] text-muted-foreground">locked</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
