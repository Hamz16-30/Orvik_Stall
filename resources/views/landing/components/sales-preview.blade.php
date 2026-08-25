<div
    x-data="{
        cart: [
            { id: 1, name: 'Artisan Leather Wallet', price: 35.00, qty: 1, sku: 'ALW-01' },
            { id: 2, name: 'Ceramic Brew Mug', price: 24.00, qty: 2, sku: 'CBM-09' }
        ],
        products: [
            { id: 1, name: 'Artisan Leather Wallet', price: 35.00, sku: 'ALW-01', cat: 'Accessories' },
            { id: 2, name: 'Ceramic Brew Mug', price: 24.00, sku: 'CBM-09', cat: 'Drinkware' },
            { id: 3, name: 'Linen Canvas Tote', price: 25.00, sku: 'LCT-14', cat: 'Bags' },
            { id: 4, name: 'Matcha Tea Tin (50g)', price: 18.50, sku: 'MTT-03', cat: 'Consumables' },
            { id: 5, name: 'Beeswax Candle 200ml', price: 16.00, sku: 'BWC-08', cat: 'Home' },
            { id: 6, name: 'Handcrafted Soap Bar', price: 8.50, sku: 'HSB-22', cat: 'Care' }
        ],
        search: '',
        activeCat: 'All',
        addToCart(prod) {
            let item = this.cart.find(i => i.id === prod.id);
            if (item) {
                item.qty++;
            } else {
                this.cart.push({ id: prod.id, name: prod.name, price: prod.price, qty: 1, sku: prod.sku });
            }
            if (window.Alpine && window.Alpine.store('toast')) {
                Alpine.store('toast').show('Added ' + prod.name + ' to cart', 'success', 2000);
            }
        },
        updateQty(id, delta) {
            let item = this.cart.find(i => i.id === id);
            if (item) {
                item.qty += delta;
                if (item.qty <= 0) {
                    this.cart = this.cart.filter(i => i.id !== id);
                }
            }
        },
        get subtotal() {
            return this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        },
        completeOrder() {
            if (this.cart.length === 0) return;
            if (window.Alpine && window.Alpine.store('toast')) {
                Alpine.store('toast').show('Sale completed! Total: $' + this.subtotal.toFixed(2), 'success');
            }
            this.cart = [];
        }
    }"
    class="bg-white rounded-2xl border border-border/80 shadow-sm overflow-hidden select-none"
>
    <!-- POS Mock Top Bar -->
    <div class="px-5 py-3.5 bg-secondary/60 border-b border-border/70 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-foreground">Active Register: Stall #04</span>
            <span class="text-[11px] text-muted-foreground hidden sm:inline">&bull; 1-Tap Checkout Mode</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-accent/10 text-accent font-semibold px-2.5 py-1 rounded-full border border-accent/20">
                Interactive Preview
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12">
        <!-- Products Left (7 cols) -->
        <div class="lg:col-span-7 p-4 sm:p-6 border-b lg:border-b-0 lg:border-r border-border/70 space-y-4">
            <!-- Search & Filters -->
            <div class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search products by name or SKU..."
                        class="w-full pl-10 pr-4 py-2 text-xs rounded-lg border border-border bg-secondary/30 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent"
                    />
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                <template x-for="prod in products.filter(p => p.name.toLowerCase().includes(search.toLowerCase()))" :key="prod.id">
                    <button
                        type="button"
                        @click="addToCart(prod)"
                        class="p-3 rounded-xl border border-border/80 bg-white hover:border-accent hover:shadow-xs transition-all text-left flex flex-col justify-between min-h-[96px] group active:scale-98"
                    >
                        <div>
                            <span class="text-[10px] text-muted-foreground uppercase font-semibold block" x-text="prod.sku"></span>
                            <span class="text-xs font-semibold text-foreground group-hover:text-accent transition-colors line-clamp-2 mt-0.5" x-text="prod.name"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs font-bold text-foreground" x-text="'$' + prod.price.toFixed(2)"></span>
                            <span class="w-5 h-5 rounded-md bg-secondary flex items-center justify-center text-foreground/70 group-hover:bg-accent group-hover:text-white transition-colors text-xs font-bold">
                                +
                            </span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <!-- Cart Right (5 cols) -->
        <div class="lg:col-span-5 p-4 sm:p-6 bg-secondary/15 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-border/60">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-accent"></i>
                        <h4 class="text-sm font-bold text-foreground">Current Order</h4>
                    </div>
                    <span class="text-xs text-muted-foreground font-medium" x-text="cart.reduce((s, i) => s + i.qty, 0) + ' items'"></span>
                </div>

                <!-- Cart Items List -->
                <div class="mt-3 space-y-2.5 max-h-56 overflow-y-auto pr-1">
                    <template x-if="cart.length === 0">
                        <div class="py-8 text-center text-xs text-muted-foreground">
                            <i data-lucide="shopping-bag" class="w-8 h-8 mx-auto text-muted-foreground/50 mb-2"></i>
                            Cart is empty. Tap any product on the left to add.
                        </div>
                    </template>

                    <template x-for="item in cart" :key="item.id">
                        <div class="p-2.5 rounded-lg bg-white border border-border/70 flex items-center justify-between gap-2 shadow-2xs">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-foreground truncate" x-text="item.name"></p>
                                <p class="text-[10px] text-muted-foreground" x-text="'$' + item.price.toFixed(2) + ' each'"></p>
                            </div>

                            <!-- Qty Adjust -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="updateQty(item.id, -1)" class="w-6 h-6 rounded bg-secondary hover:bg-muted text-foreground flex items-center justify-center text-xs font-bold">-</button>
                                <span class="text-xs font-bold w-4 text-center" x-text="item.qty"></span>
                                <button type="button" @click="updateQty(item.id, 1)" class="w-6 h-6 rounded bg-secondary hover:bg-muted text-foreground flex items-center justify-center text-xs font-bold">+</button>
                            </div>

                            <!-- Line Total -->
                            <div class="text-right shrink-0 min-w-[50px]">
                                <span class="text-xs font-bold text-foreground" x-text="'$' + (item.price * item.qty).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Totals & Checkout -->
            <div class="mt-4 pt-4 border-t border-border/70 space-y-3">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span class="font-medium text-foreground" x-text="'$' + subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Stall Tax (0%)</span>
                        <span class="font-medium text-foreground">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-foreground pt-1 border-t border-border/50">
                        <span>Total Due</span>
                        <span class="text-base text-accent" x-text="'$' + subtotal.toFixed(2)"></span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="completeOrder()"
                    :disabled="cart.length === 0"
                    class="w-full py-3 px-4 rounded-xl bg-accent text-accent-foreground font-semibold text-sm shadow-md shadow-accent/20 hover:bg-accent/90 active:scale-98 transition-all disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2"
                >
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Complete Sale (<span x-text="'$' + subtotal.toFixed(2)"></span>)</span>
                </button>
            </div>
        </div>
    </div>
</div>
