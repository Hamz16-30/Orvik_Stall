@php
    $canViewCost = $user->isOwner() || $user->isManager();
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Edit Product: {{ $product->name }}" subtitle="Modify product details, category, and pricing" activeNav="inventory">
    <div class="max-w-3xl space-y-6">
        <!-- Back Navigation -->
        <a href="{{ route('app.inventory.show', $product->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Product Details</span>
        </a>

        <form method="POST" action="{{ route('app.inventory.update', $product->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- 1. Essential Information -->
            <x-ui.card title="Product Information" description="Primary details used by POS and daily sales">
                <div class="space-y-4">
                    <x-ui.input
                        label="Product Name"
                        name="name"
                        id="name"
                        value="{{ old('name', $product->name) }}"
                        required
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input
                            label="Selling Price ({{ $currency }})"
                            type="number"
                            step="0.01"
                            name="selling_price"
                            id="selling_price"
                            value="{{ old('selling_price', $product->selling_price) }}"
                            required
                        />

                        @if($canViewCost)
                            <x-ui.input
                                label="Cost Price ({{ $currency }}) [Optional]"
                                type="number"
                                step="0.01"
                                name="cost_price"
                                id="cost_price"
                                value="{{ old('cost_price', $product->cost_price) }}"
                            />
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <!-- 2. Stock Policy & Units -->
            <x-ui.card title="Stock Configuration" description="Current inventory counts must be modified through Stock Adjustments">
                <div class="space-y-4">
                    <div class="p-3 bg-secondary/50 rounded-xl border border-border flex items-center justify-between text-xs">
                        <div>
                            <span class="text-muted-foreground">Current Stock on Hand:</span>
                            <span class="font-mono font-bold text-foreground ml-1">{{ (float)$product->stock_quantity }} {{ $product->unit }}</span>
                        </div>
                        <a href="{{ route('app.inventory.show', $product->id) }}" class="text-accent font-semibold hover:underline">
                            Adjust Stock &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input
                            label="Low Stock Alert Level"
                            type="number"
                            step="0.001"
                            name="low_stock_threshold"
                            id="low_stock_threshold"
                            value="{{ old('low_stock_threshold', (float)$product->low_stock_threshold) }}"
                        />

                        <x-ui.select
                            label="Unit"
                            name="unit"
                            id="unit"
                            :options="[
                                'piece' => 'piece / unit',
                                'pack' => 'pack / packet',
                                'bottle' => 'bottle',
                                'can' => 'can',
                                'box' => 'box',
                                'cup' => 'cup',
                                'kg' => 'kg (Kilogram)',
                                'gram' => 'gram',
                                'liter' => 'liter',
                                'dozen' => 'dozen',
                            ]"
                            selected="{{ old('unit', $product->unit) }}"
                        />
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-foreground font-medium">
                            <input
                                type="checkbox"
                                name="track_inventory"
                                value="1"
                                class="rounded border-border text-accent focus:ring-accent"
                                {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}
                            />
                            <span>Track inventory for this item</span>
                        </label>
                    </div>
                </div>
            </x-ui.card>

            <!-- 3. Category & Identifiers -->
            <x-ui.card title="Category & Barcode" description="Optional SKU and organization">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-ui.select
                            label="Category"
                            name="category_id"
                            id="category_id"
                        >
                            <option value="">Uncategorized</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input
                            label="SKU Code"
                            name="sku"
                            id="sku"
                            value="{{ old('sku', $product->sku) }}"
                        />

                        <x-ui.input
                            label="Barcode"
                            name="barcode"
                            id="barcode"
                            value="{{ old('barcode', $product->barcode) }}"
                        />
                    </div>

                    <x-ui.textarea
                        label="Product Description"
                        name="description"
                        id="description"
                        rows="2"
                        value="{{ old('description', $product->description) }}"
                    />
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="ghost" size="md" :href="route('app.inventory.show', $product->id)">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" size="md" rounded="lg">
                    Update Product
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
