<div class="bg-white rounded-2xl border border-border/80 shadow-sm overflow-hidden select-none">
    <!-- Top Bar -->
    <div class="px-5 py-3.5 bg-secondary/60 border-b border-border/70 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="boxes" class="w-4 h-4 text-accent"></i>
            <span class="text-xs font-bold text-foreground">Stall Inventory Watch</span>
            <span class="text-[11px] text-muted-foreground hidden sm:inline">&bull; 124 Active Catalog SKUs</span>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.badge variant="low-stock" size="sm">3 Low Stock</x-ui.badge>
            <x-ui.badge variant="destructive" size="sm">1 Out of Stock</x-ui.badge>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-secondary/40 text-muted-foreground uppercase text-[10px] tracking-wider border-b border-border">
                <tr>
                    <th class="px-4 py-3 font-semibold">Product Name</th>
                    <th class="px-4 py-3 font-semibold">SKU</th>
                    <th class="px-4 py-3 font-semibold">Stock Qty</th>
                    <th class="px-4 py-3 font-semibold">Price</th>
                    <th class="px-4 py-3 font-semibold">Movement Status</th>
                    <th class="px-4 py-3 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/60 text-foreground">
                <tr class="hover:bg-muted/30 transition-colors">
                    <td class="px-4 py-3.5 font-semibold text-foreground">Artisan Leather Wallet</td>
                    <td class="px-4 py-3.5 text-muted-foreground font-mono">ALW-01</td>
                    <td class="px-4 py-3.5 font-bold text-foreground">42 in stock</td>
                    <td class="px-4 py-3.5 font-bold">$35.00</td>
                    <td class="px-4 py-3.5">
                        <x-ui.badge variant="active" size="sm">In Stock &bull; Fast Velocity</x-ui.badge>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-accent font-semibold hover:underline cursor-pointer">Edit</span>
                    </td>
                </tr>

                <tr class="hover:bg-muted/30 transition-colors bg-amber-50/20">
                    <td class="px-4 py-3.5 font-semibold text-foreground">Ceramic Brew Mug (Matte)</td>
                    <td class="px-4 py-3.5 text-muted-foreground font-mono">CBM-09</td>
                    <td class="px-4 py-3.5 font-bold text-amber-700">3 in stock (Min: 10)</td>
                    <td class="px-4 py-3.5 font-bold">$24.00</td>
                    <td class="px-4 py-3.5">
                        <x-ui.badge variant="low-stock" size="sm">Low Stock Alert</x-ui.badge>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-accent font-semibold hover:underline cursor-pointer">Reorder</span>
                    </td>
                </tr>

                <tr class="hover:bg-muted/30 transition-colors">
                    <td class="px-4 py-3.5 font-semibold text-foreground">Organic Matcha Powder 50g</td>
                    <td class="px-4 py-3.5 text-muted-foreground font-mono">OMP-02</td>
                    <td class="px-4 py-3.5 font-bold text-rose-600">0 in stock</td>
                    <td class="px-4 py-3.5 font-bold">$18.50</td>
                    <td class="px-4 py-3.5">
                        <x-ui.badge variant="destructive" size="sm">Out of Stock</x-ui.badge>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-accent font-semibold hover:underline cursor-pointer">Restock</span>
                    </td>
                </tr>

                <tr class="hover:bg-muted/30 transition-colors bg-rose-50/20">
                    <td class="px-4 py-3.5 font-semibold text-foreground">Vintage Brass Keyring</td>
                    <td class="px-4 py-3.5 text-muted-foreground font-mono">VBK-44</td>
                    <td class="px-4 py-3.5 font-bold text-foreground">18 in stock</td>
                    <td class="px-4 py-3.5 font-bold">$15.00</td>
                    <td class="px-4 py-3.5">
                        <x-ui.badge variant="warning" size="sm">Dead Stock &bull; 64 Days Idle</x-ui.badge>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-rose-600 font-semibold hover:underline cursor-pointer">Discount</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
