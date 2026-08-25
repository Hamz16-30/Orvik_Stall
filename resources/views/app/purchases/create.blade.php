@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Record Purchase" subtitle="Ingest new inventory shipments from suppliers" activeNav="purchases">
    <div
        x-data="{
            supplierId: '{{ old('supplier_id', '') }}',
            items: [
                { product_id: '', quantity: 1, unit_cost: 0 }
            ],
            discount: 0,
            tax: 0,
            paymentMethod: 'cash',
            paymentStatus: 'paid',
            products: {{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'cost' => (float)($p->cost_price ?? 0), 'stock' => (float)$p->stock_quantity, 'unit' => $p->unit])) }},

            addItem() {
                this.items.push({ product_id: '', quantity: 1, unit_cost: 0 });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            onProductChange(item) {
                const prod = this.products.find(p => p.id == item.product_id);
                if (prod) {
                    item.unit_cost = prod.cost;
                }
            },

            get subtotal() {
                return this.items.reduce((sum, i) => sum + ((parseFloat(i.quantity) || 0) * (parseFloat(i.unit_cost) || 0)), 0);
            },

            get total() {
                return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0) + (parseFloat(this.tax) || 0));
            }
        }"
        class="max-w-4xl space-y-6"
    >
        <a href="{{ route('app.purchases.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Purchases</span>
        </a>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                <p class="font-bold">Please correct the following errors:</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('app.purchases.store') }}" class="space-y-6">
            @csrf

            <!-- Supplier & Purchase Info -->
            <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
                <h3 class="text-base font-bold text-foreground">Purchase Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="supplier_id" class="block text-xs font-semibold text-foreground mb-1.5">Supplier / Vendor</label>
                        <select
                            name="supplier_id"
                            id="supplier_id"
                            x-model="supplierId"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                            <option value="">Select Vendor (Optional)</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} {{ $sup->company_name ? "({$sup->company_name})" : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="purchased_at" class="block text-xs font-semibold text-foreground mb-1.5">Purchase Date</label>
                        <input
                            type="date"
                            name="purchased_at"
                            id="purchased_at"
                            value="{{ old('purchased_at', now()->toDateString()) }}"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>

                    <div>
                        <label for="payment_status" class="block text-xs font-semibold text-foreground mb-1.5">Payment Status</label>
                        <select
                            name="payment_status"
                            id="payment_status"
                            x-model="paymentStatus"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                            <option value="paid">Paid in Full</option>
                            <option value="partial">Partial</option>
                            <option value="unpaid">Unpaid / Credit</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-foreground">Items to Ingest</h3>
                        <p class="text-xs text-muted-foreground">Select products, received quantities, and unit purchase cost</p>
                    </div>
                    <button
                        type="button"
                        @click="addItem()"
                        class="px-3 py-1.5 rounded-lg bg-secondary hover:bg-muted text-foreground text-xs font-bold flex items-center gap-1.5"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Row</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-border text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                                <th class="pb-2 min-w-[200px]">Product</th>
                                <th class="pb-2 w-28">Quantity</th>
                                <th class="pb-2 w-32">Unit Cost ({{ $currency }})</th>
                                <th class="pb-2 w-32 text-right">Line Total</th>
                                <th class="pb-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="py-2.5 pr-3">
                                        <select
                                            :name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(item)"
                                            required
                                            class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-border bg-white text-foreground focus:outline-none focus:ring-1 focus:ring-accent"
                                        >
                                            <option value="">Select Product...</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id" x-text="p.name + ' (Cur. Stock: ' + p.stock + ')'"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            :name="'items[' + index + '][quantity]'"
                                            x-model.number="item.quantity"
                                            required
                                            class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-border bg-white text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-accent"
                                        />
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :name="'items[' + index + '][unit_cost]'"
                                            x-model.number="item.unit_cost"
                                            required
                                            class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-border bg-white text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-accent"
                                        />
                                    </td>
                                    <td class="py-2.5 text-right font-mono font-bold text-foreground pr-2" x-text="'{{ $currency }}' + ((item.quantity || 0) * (item.unit_cost || 0)).toFixed(2)"></td>
                                    <td class="py-2.5 text-center">
                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            x-show="items.length > 1"
                                            class="p-1 text-muted-foreground hover:text-rose-600"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Summary -->
                <div class="pt-4 border-t border-border flex flex-col sm:flex-row justify-between gap-4 items-start">
                    <div class="w-full sm:w-1/2 space-y-3">
                        <div>
                            <label for="payment_method" class="block text-xs font-semibold text-foreground mb-1">Payment Method</label>
                            <select
                                name="payment_method"
                                id="payment_method"
                                x-model="paymentMethod"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                            >
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="mobile_payment">Mobile Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-semibold text-foreground mb-1">Purchase Notes</label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="2"
                                placeholder="Invoice reference number, batch details..."
                                class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="w-full sm:w-72 space-y-2 text-xs">
                        <div class="flex justify-between text-muted-foreground">
                            <span>Subtotal:</span>
                            <span class="font-mono font-semibold text-foreground" x-text="'{{ $currency }}' + subtotal.toFixed(2)"></span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-muted-foreground">Discount:</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="discount_amount"
                                x-model.number="discount"
                                class="w-24 px-2 py-1 text-right text-xs rounded border border-border font-mono"
                            />
                        </div>

                        <div class="pt-2 border-t border-border flex justify-between text-base font-extrabold text-foreground">
                            <span>Total Payable:</span>
                            <span class="font-mono" x-text="'{{ $currency }}' + total.toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" :href="route('app.purchases.index')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                        Complete Purchase & Add Stock
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
