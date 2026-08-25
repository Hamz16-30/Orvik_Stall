@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Point of Sale" subtitle="Rapid 1-tap checkout register" activeNav="sales">
    <div
        x-data="{
            cart: [],
            discount: 0,
            tax: 0,
            paymentMethod: 'cash',
            amountReceived: null,
            customerId: null,
            customerName: null,
            notes: '',
            searchQuery: '{{ request('search', '') }}',
            selectedCategoryId: '{{ request('category_id', 'all') }}',
            isSubmitting: false,
            mobileCartOpen: false,
            paymentModalOpen: false,
            successModalOpen: false,
            quickCustomerModalOpen: false,
            newCustName: '',
            newCustPhone: '',
            isSavingCustomer: false,
            completedSale: null,
            idempotencyToken: 'pos_' + Math.random().toString(36).substring(2) + Date.now(),

            init() {
                const saved = sessionStorage.getItem('orvik_pos_cart');
                if (saved) {
                    try { this.cart = JSON.parse(saved); } catch(e) {}
                }
                this.$watch('cart', (val) => {
                    sessionStorage.setItem('orvik_pos_cart', JSON.stringify(val));
                });
            },

            addToCart(product) {
                if (product.track_inventory && product.stock_quantity <= 0) {
                    return;
                }
                const existing = this.cart.find(item => item.id === product.id);
                if (existing) {
                    if (product.track_inventory && existing.quantity >= product.stock_quantity) {
                        alert('Cannot add more. Maximum available stock on hand: ' + product.stock_quantity);
                        return;
                    }
                    existing.quantity += 1;
                } else {
                    this.cart.push({
                        id: product.id,
                        name: product.name,
                        unit_price: parseFloat(product.selling_price),
                        quantity: 1,
                        stock_quantity: parseFloat(product.stock_quantity),
                        track_inventory: Boolean(product.track_inventory),
                        unit: product.unit || 'piece',
                        sku: product.sku || ''
                    });
                }
            },

            increment(item) {
                if (item.track_inventory && item.quantity >= item.stock_quantity) {
                    alert('Cannot exceed available stock of ' + item.stock_quantity);
                    return;
                }
                item.quantity += 1;
            },

            decrement(item) {
                if (item.quantity > 1) {
                    item.quantity -= 1;
                } else {
                    this.removeFromCart(item);
                }
            },

            removeFromCart(item) {
                this.cart = this.cart.filter(i => i.id !== item.id);
            },

            clearCart() {
                if (this.cart.length === 0) return;
                if (confirm('Clear all items from current cart?')) {
                    this.cart = [];
                    this.discount = 0;
                    this.amountReceived = null;
                    this.customerId = null;
                    this.customerName = null;
                }
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            },

            get total() {
                const total = this.subtotal - parseFloat(this.discount || 0) + parseFloat(this.tax || 0);
                return Math.max(0, total);
            },

            get change() {
                if (this.paymentMethod !== 'cash' || this.amountReceived === null || this.amountReceived === '') {
                    return 0;
                }
                return Math.max(0, parseFloat(this.amountReceived) - this.total);
            },

            setQuickCash(amount) {
                this.amountReceived = amount;
            },

            openPaymentModal() {
                if (this.cart.length === 0) return;
                if (this.paymentMethod === 'cash' && !this.amountReceived) {
                    this.amountReceived = Math.ceil(this.total);
                }
                this.paymentModalOpen = true;
            },

            async saveQuickCustomer() {
                if (!this.newCustName.trim()) {
                    alert('Customer name is required.');
                    return;
                }
                this.isSavingCustomer = true;
                try {
                    const res = await fetch('{{ route('app.customers.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: this.newCustName,
                            phone: this.newCustPhone
                        })
                    });
                    const data = await res.json();
                    if (data.success && data.customer) {
                        this.customerId = data.customer.id;
                        this.customerName = data.customer.name;
                        this.quickCustomerModalOpen = false;
                        this.newCustName = '';
                        this.newCustPhone = '';
                    } else {
                        alert(data.message || 'Failed to create customer.');
                    }
                } catch (e) {
                    alert('Network error while saving customer.');
                } finally {
                    this.isSavingCustomer = false;
                }
            },

            async completeSale() {
                if (this.isSubmitting || this.cart.length === 0) return;

                if (this.paymentMethod === 'cash' && this.amountReceived && parseFloat(this.amountReceived) < this.total) {
                    alert('Amount received is less than total order amount.');
                    return;
                }

                this.isSubmitting = true;

                try {
                    const payload = {
                        items: this.cart.map(i => ({ product_id: i.id, quantity: i.quantity })),
                        customer_id: this.customerId,
                        payment_method: this.paymentMethod,
                        amount_received: this.amountReceived ? parseFloat(this.amountReceived) : null,
                        discount_amount: parseFloat(this.discount || 0),
                        tax_amount: parseFloat(this.tax || 0),
                        idempotency_token: this.idempotencyToken,
                        notes: this.notes
                    };

                    const response = await fetch('{{ route('app.sales.complete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        alert(result.message || (result.errors ? Object.values(result.errors).flat().join('\n') : 'Transaction failed. Please try again.'));
                        this.isSubmitting = false;
                        return;
                    }

                    // Success!
                    this.completedSale = result.sale;
                    this.cart = [];
                    this.discount = 0;
                    this.notes = '';
                    this.amountReceived = null;
                    this.customerId = null;
                    this.customerName = null;
                    this.paymentModalOpen = false;
                    this.mobileCartOpen = false;
                    this.successModalOpen = true;
                    this.idempotencyToken = 'pos_' + Math.random().toString(36).substring(2) + Date.now();
                    sessionStorage.removeItem('orvik_pos_cart');

                } catch (error) {
                    console.error('POS Checkout Error:', error);
                    alert('Network or server error during checkout. Please verify transaction.');
                } finally {
                    this.isSubmitting = false;
                }
            },

            resetForNewSale() {
                this.successModalOpen = false;
                this.completedSale = null;
            }
        }"
        class="relative"
    >
        <!-- Desktop 2-Column POS Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Side: Catalog, Search & Categories (65% width) -->
            <div class="lg:col-span-8 space-y-4">
                <!-- Search & Header Bar -->
                <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input
                                type="text"
                                placeholder="Search products by name, SKU, or barcode... (Press / to focus)"
                                x-model="searchQuery"
                                @keydown.slash.window.prevent="$el.focus()"
                                class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                            />
                            <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.button variant="secondary" size="sm" rounded="lg" icon="history" :href="route('app.sales.history')">
                                History
                            </x-ui.button>
                        </div>
                    </div>

                    <!-- Category Pills Horizontal Scroll -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        <button
                            type="button"
                            @click="selectedCategoryId = 'all'"
                            :class="selectedCategoryId === 'all' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all shrink-0 select-none"
                        >
                            All Items
                        </button>

                        @foreach($categories as $cat)
                            <button
                                type="button"
                                @click="selectedCategoryId = '{{ $cat->id }}'"
                                :class="selectedCategoryId === '{{ $cat->id }}' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                class="px-3.5 py-1.5 rounded-full text-xs transition-all shrink-0 select-none"
                            >
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Interactive 1-Tap Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                    @forelse($products as $product)
                        @php
                            $isOut = $product->track_inventory && (float)$product->stock_quantity <= 0;
                            $isLow = $product->track_inventory && !$isOut && (float)$product->stock_quantity <= (float)$product->low_stock_threshold;
                        @endphp
                        <div
                            x-show="(selectedCategoryId === 'all' || selectedCategoryId === '{{ $product->category_id }}') &&
                                    ('{{ strtolower(addslashes($product->name)) }}'.includes(searchQuery.toLowerCase()) ||
                                     '{{ strtolower(addslashes($product->sku ?? '')) }}'.includes(searchQuery.toLowerCase()) ||
                                     '{{ strtolower(addslashes($product->barcode ?? '')) }}'.includes(searchQuery.toLowerCase()))"
                            @click="{{ $isOut ? '' : "addToCart(" . json_encode([
                                'id' => $product->id,
                                'name' => $product->name,
                                'selling_price' => (float)$product->selling_price,
                                'stock_quantity' => (float)$product->stock_quantity,
                                'track_inventory' => (bool)$product->track_inventory,
                                'unit' => $product->unit,
                                'sku' => $product->sku
                            ]) . ")" }}"
                            class="bg-white p-3.5 rounded-2xl border transition-all text-left flex flex-col justify-between select-none relative group min-h-[110px] {{ $isOut ? 'opacity-60 bg-muted/40 cursor-not-allowed border-border/60' : 'cursor-pointer border-border hover:border-accent hover:shadow-xs active:scale-[0.98]' }}"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-1.5">
                                    <h4 class="font-bold text-foreground text-xs leading-snug group-hover:text-accent line-clamp-2">
                                        {{ $product->name }}
                                    </h4>
                                </div>
                                <p class="text-[10px] text-muted-foreground mt-0.5 font-mono">
                                    {{ $product->sku ?: ($product->category ? $product->category->name : 'Item') }}
                                </p>
                            </div>

                            <div class="mt-3 pt-2 border-t border-border/50 flex items-end justify-between gap-1">
                                <div>
                                    <span class="text-xs font-extrabold text-foreground block">
                                        {{ $currency }}{{ number_format($product->selling_price, 2) }}
                                    </span>
                                </div>

                                <div>
                                    @if($isOut)
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-700">Out</span>
                                    @elseif($isLow)
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">
                                            {{ (float)$product->stock_quantity }} left
                                        </span>
                                    @elseif($product->track_inventory)
                                        <span class="text-[9px] text-muted-foreground font-mono">
                                            {{ (float)$product->stock_quantity }} {{ $product->unit }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white p-12 rounded-2xl border border-border text-center space-y-2">
                            <p class="text-sm font-bold text-foreground">No sellable products found</p>
                            <p class="text-xs text-muted-foreground">Add products in the inventory section to start selling.</p>
                            <div class="pt-2">
                                <x-ui.button variant="accent" size="sm" rounded="lg" :href="route('app.inventory.create')">
                                    Add Product
                                </x-ui.button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Side: Sticky Desktop Cart & Payment (35% width) -->
            <div class="hidden lg:block lg:col-span-4 sticky top-20">
                <div class="bg-white rounded-2xl border border-border/80 shadow-xs flex flex-col h-[calc(100vh-100px)]">
                    <!-- Cart Header & Quick Customer -->
                    <div class="p-4 border-b border-border space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-accent text-white flex items-center justify-center font-bold text-xs">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-foreground">Current Order</h3>
                                    <p class="text-[10px] text-muted-foreground" x-text="cart.length + ' item(s)'"></p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="clearCart()"
                                x-show="cart.length > 0"
                                class="text-xs text-rose-600 hover:underline font-semibold"
                            >
                                Clear
                            </button>
                        </div>

                        <!-- Customer Tagging Bar -->
                        <div class="pt-1.5 border-t border-border/50 flex items-center justify-between text-xs">
                            <template x-if="!customerId">
                                <button
                                    type="button"
                                    @click="quickCustomerModalOpen = true"
                                    class="inline-flex items-center gap-1.5 text-accent hover:underline font-semibold"
                                >
                                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                    <span>+ Add Customer</span>
                                </button>
                            </template>
                            <template x-if="customerId">
                                <div class="flex items-center justify-between w-full bg-secondary/60 px-2.5 py-1 rounded-lg">
                                    <span class="font-bold text-foreground truncate" x-text="'👤 ' + customerName"></span>
                                    <button type="button" @click="customerId = null; customerName = null" class="text-muted-foreground hover:text-foreground ml-2">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Cart Item List (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-2.5 divide-y divide-border/40">
                        <template x-if="cart.length === 0">
                            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-muted-foreground space-y-2">
                                <i data-lucide="shopping-bag" class="w-8 h-8 text-muted-foreground/50"></i>
                                <p class="text-xs font-medium">Cart is empty</p>
                                <p class="text-[10px]">Tap products on the left to ring up sales</p>
                            </div>
                        </template>

                        <template x-for="item in cart" :key="item.id">
                            <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3 text-xs">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-foreground truncate" x-text="item.name"></p>
                                    <p class="text-[10px] text-muted-foreground font-mono" x-text="'{{ $currency }}' + item.unit_price.toFixed(2) + ' / ' + item.unit"></p>
                                </div>

                                <!-- Quantity Adjusters -->
                                <div class="flex items-center gap-1.5 bg-secondary/80 rounded-lg p-0.5 border border-border/50 shrink-0">
                                    <button
                                        type="button"
                                        @click="decrement(item)"
                                        class="w-6 h-6 rounded bg-white text-foreground hover:bg-muted font-bold flex items-center justify-center text-xs shadow-2xs"
                                    >
                                        -
                                    </button>
                                    <span class="w-6 text-center font-mono font-bold text-foreground text-xs" x-text="item.quantity"></span>
                                    <button
                                        type="button"
                                        @click="increment(item)"
                                        class="w-6 h-6 rounded bg-white text-foreground hover:bg-muted font-bold flex items-center justify-center text-xs shadow-2xs"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Line Total -->
                                <div class="text-right shrink-0 w-16">
                                    <span class="font-bold text-foreground font-mono" x-text="'{{ $currency }}' + (item.unit_price * item.quantity).toFixed(2)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Cart Footer / Financial Breakdown & CTA -->
                    <div class="p-4 border-t border-border bg-muted/10 space-y-3">
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span>Subtotal:</span>
                            <span class="font-mono font-semibold text-foreground" x-text="'{{ $currency }}' + subtotal.toFixed(2)"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs gap-3">
                            <span class="text-muted-foreground">Discount ({{ $currency }}):</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                x-model="discount"
                                class="w-24 px-2 py-1 text-right text-xs rounded border border-border bg-white text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-accent"
                            />
                        </div>

                        <div class="pt-2 border-t border-border/80 flex items-baseline justify-between">
                            <span class="text-sm font-bold text-foreground">Total Payable:</span>
                            <span class="text-lg font-extrabold text-foreground font-mono tracking-tight" x-text="'{{ $currency }}' + total.toFixed(2)"></span>
                        </div>

                        <button
                            type="button"
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0 || isSubmitting"
                            class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-accent hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md active:scale-[0.99] flex items-center justify-center gap-2"
                        >
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Proceed to Payment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Customer Modal -->
        <div x-show="quickCustomerModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="quickCustomerModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl border border-border shadow-2xl max-w-sm w-full p-6 text-left space-y-4" @click.stop>
                    <div class="flex items-center justify-between pb-2 border-b border-border">
                        <h3 class="text-base font-bold text-foreground">+ Quick Customer</h3>
                        <button type="button" @click="quickCustomerModalOpen = false" class="text-muted-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <x-ui.input label="Customer Name" name="cust_name" id="cust_name" x-model="newCustName" placeholder="e.g. Tariq Mehmood" required />
                        <x-ui.input label="Phone Number" name="cust_phone" id="cust_phone" type="tel" x-model="newCustPhone" placeholder="e.g. 03001234567" />
                    </div>

                    <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                        <x-ui.button type="button" variant="ghost" size="sm" @click="quickCustomerModalOpen = false">Cancel</x-ui.button>
                        <button
                            type="button"
                            @click="saveQuickCustomer()"
                            :disabled="isSavingCustomer"
                            class="px-4 py-2 bg-accent text-white font-bold text-xs rounded-xl hover:bg-accent/90"
                        >
                            <span x-text="isSavingCustomer ? 'Saving...' : 'Add to Order'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Floating Bottom Action Bar -->
        <div class="lg:hidden fixed bottom-16 left-4 right-4 z-40">
            <button
                type="button"
                @click="mobileCartOpen = true"
                x-show="cart.length > 0"
                x-transition
                class="w-full py-3.5 px-5 rounded-2xl font-bold text-sm text-white bg-accent shadow-xl flex items-center justify-between active:scale-[0.98] transition-transform"
            >
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span x-text="cart.reduce((sum, i) => sum + i.quantity, 0) + ' items in cart'"></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-base" x-text="'{{ $currency }}' + total.toFixed(2)"></span>
                    <i data-lucide="chevron-up" class="w-4 h-4"></i>
                </div>
            </button>
        </div>

        <!-- Mobile Slide-Up Cart Drawer / Bottom Sheet -->
        <div x-show="mobileCartOpen" x-cloak class="lg:hidden relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="mobileCartOpen = false"></div>
            <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white rounded-t-3xl border-t border-border shadow-2xl p-5 flex flex-col text-left" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-foreground">Current Cart</h3>
                        <span class="text-xs text-muted-foreground" x-text="'(' + cart.length + ' items)'"></span>
                    </div>
                    <button type="button" @click="mobileCartOpen = false" class="p-1 text-muted-foreground">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto py-3 space-y-3">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex items-center justify-between text-xs py-1 border-b border-border/40">
                            <div>
                                <p class="font-bold text-foreground" x-text="item.name"></p>
                                <p class="text-[10px] text-muted-foreground" x-text="'{{ $currency }}' + item.unit_price.toFixed(2)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1 bg-secondary rounded-lg p-0.5">
                                    <button type="button" @click="decrement(item)" class="w-7 h-7 bg-white rounded font-bold text-xs">-</button>
                                    <span class="w-6 text-center font-bold" x-text="item.quantity"></span>
                                    <button type="button" @click="increment(item)" class="w-7 h-7 bg-white rounded font-bold text-xs">+</button>
                                </div>
                                <span class="font-bold text-foreground font-mono w-16 text-right" x-text="'{{ $currency }}' + (item.unit_price * item.quantity).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-3 border-t border-border space-y-3">
                    <div class="flex justify-between text-sm font-bold text-foreground">
                        <span>Total:</span>
                        <span class="font-mono text-base" x-text="'{{ $currency }}' + total.toFixed(2)"></span>
                    </div>

                    <button
                        type="button"
                        @click="openPaymentModal()"
                        class="w-full py-3.5 rounded-xl font-bold text-sm text-white bg-accent shadow-md flex items-center justify-center gap-2"
                    >
                        <span>Select Payment & Checkout</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Selection Modal -->
        <div x-show="paymentModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="paymentModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl border border-border shadow-2xl max-w-lg w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <div>
                            <h3 class="text-base font-bold text-foreground">Checkout & Payment</h3>
                            <p class="text-xs text-muted-foreground">Select payment method and confirm transaction</p>
                        </div>
                        <button type="button" @click="paymentModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Total Display -->
                    <div class="my-4 p-4 rounded-2xl bg-secondary/50 border border-border flex items-center justify-between">
                        <div>
                            <p class="text-[11px] text-muted-foreground font-medium">Total Amount Due</p>
                            <p class="text-2xl font-extrabold text-foreground font-mono tracking-tight mt-0.5" x-text="'{{ $currency }}' + total.toFixed(2)"></p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-accent/10 text-accent" x-text="cart.reduce((s, i) => s + i.quantity, 0) + ' item(s)'"></span>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-foreground mb-2">Payment Method</label>
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 text-xs">
                                <button
                                    type="button"
                                    @click="paymentMethod = 'cash'"
                                    :class="paymentMethod === 'cash' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                    class="py-2.5 px-2 rounded-xl text-center transition-colors"
                                >
                                    💵 Cash
                                </button>
                                <button
                                    type="button"
                                    @click="paymentMethod = 'card'"
                                    :class="paymentMethod === 'card' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                    class="py-2.5 px-2 rounded-xl text-center transition-colors"
                                >
                                    💳 Card
                                </button>
                                <button
                                    type="button"
                                    @click="paymentMethod = 'mobile_payment'"
                                    :class="paymentMethod === 'mobile_payment' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                    class="py-2.5 px-2 rounded-xl text-center transition-colors"
                                >
                                    📱 Mobile
                                </button>
                                <button
                                    type="button"
                                    @click="paymentMethod = 'bank_transfer'"
                                    :class="paymentMethod === 'bank_transfer' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                    class="py-2.5 px-2 rounded-xl text-center transition-colors"
                                >
                                    🏦 Bank
                                </button>
                                <button
                                    type="button"
                                    @click="paymentMethod = 'other'"
                                    :class="paymentMethod === 'other' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-secondary text-foreground hover:bg-muted'"
                                    class="py-2.5 px-2 rounded-xl text-center transition-colors"
                                >
                                    📦 Other
                                </button>
                            </div>
                        </div>

                        <!-- Cash Specific Change Calculator -->
                        <div x-show="paymentMethod === 'cash'" class="space-y-3 p-3.5 bg-secondary/30 rounded-2xl border border-border/60">
                            <div>
                                <label class="block text-xs font-semibold text-foreground mb-1">Cash Received ({{ $currency }})</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    placeholder="Enter cash received"
                                    x-model="amountReceived"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-border bg-white text-foreground font-mono focus:outline-none focus:ring-2 focus:ring-accent"
                                />
                            </div>

                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" @click="setQuickCash(Math.ceil(total))" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-border rounded-lg hover:bg-muted">Exact</button>
                                <button type="button" @click="setQuickCash(100)" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-border rounded-lg hover:bg-muted">100</button>
                                <button type="button" @click="setQuickCash(500)" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-border rounded-lg hover:bg-muted">500</button>
                                <button type="button" @click="setQuickCash(1000)" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-border rounded-lg hover:bg-muted">1,000</button>
                                <button type="button" @click="setQuickCash(5000)" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-border rounded-lg hover:bg-muted">5,000</button>
                            </div>

                            <div class="pt-2 border-t border-border/60 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground font-medium">Change to Return:</span>
                                <span class="text-base font-extrabold text-emerald-600 font-mono" x-text="'{{ $currency }}' + change.toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Complete Sale Button -->
                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="md" @click="paymentModalOpen = false">
                                Cancel
                            </x-ui.button>

                            <button
                                type="button"
                                @click="completeSale()"
                                :disabled="isSubmitting"
                                class="py-3 px-6 rounded-xl font-bold text-sm text-white bg-accent hover:bg-accent/90 disabled:opacity-50 transition-all shadow-md flex items-center gap-2"
                            >
                                <i data-lucide="check-circle-2" class="w-4 h-4" x-show="!isSubmitting"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSubmitting"></i>
                                <span x-text="isSubmitting ? 'Processing Sale...' : 'Complete Sale'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale Completion Success Modal -->
        <div x-show="successModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/50 backdrop-blur-xs"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl border border-border shadow-2xl max-w-md w-full p-6 text-center space-y-5" @click.stop>
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
                        <i data-lucide="check" class="w-8 h-8 stroke-[3]"></i>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-foreground">Sale Completed!</h3>
                        <p class="text-xs text-muted-foreground mt-1">Transaction recorded & inventory deducted</p>
                    </div>

                    <template x-if="completedSale">
                        <div class="p-4 rounded-2xl bg-secondary/40 border border-border/60 text-xs space-y-2 text-left">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Invoice Number:</span>
                                <span class="font-mono font-bold text-foreground" x-text="completedSale.invoice_number"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Total Paid:</span>
                                <span class="font-mono font-bold text-foreground" x-text="'{{ $currency }}' + completedSale.total_amount"></span>
                            </div>
                            <template x-if="completedSale.change_amount > 0">
                                <div class="flex justify-between text-emerald-700 font-semibold">
                                    <span>Change Returned:</span>
                                    <span class="font-mono" x-text="'{{ $currency }}' + completedSale.change_amount"></span>
                                </div>
                            </template>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Payment Method:</span>
                                <span class="font-semibold text-foreground" x-text="completedSale.payment_method"></span>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <template x-if="completedSale">
                            <a
                                :href="completedSale.receipt_url"
                                target="_blank"
                                class="py-2.5 px-4 rounded-xl border border-border bg-white text-foreground hover:bg-muted font-bold text-xs flex items-center justify-center gap-1.5"
                            >
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                <span>Print Receipt</span>
                            </a>
                        </template>

                        <button
                            type="button"
                            @click="resetForNewSale()"
                            class="py-2.5 px-4 rounded-xl bg-accent text-white hover:bg-accent/90 font-bold text-xs shadow-md flex items-center justify-center gap-1.5"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>New Sale</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
