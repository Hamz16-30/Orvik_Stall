@php
    $canViewCost = $user->isOwner() || $user->isManager();
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Add New Product" subtitle="Add a new retail product to your stall inventory" activeNav="inventory">
    <div class="max-w-3xl space-y-6">
        <!-- Back Navigation -->
        <a href="{{ route('app.inventory.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Inventory</span>
        </a>

        <form method="POST" action="{{ route('app.inventory.store') }}" class="space-y-6">
            @csrf

            <!-- 1. Essential Information -->
            <x-ui.card title="Product Information" description="Primary details used by POS and daily sales">
                <div class="space-y-4">
                    <x-ui.input
                        label="Product Name"
                        name="name"
                        id="name"
                        placeholder="e.g. Mineral Water 500ml"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input
                            label="Selling Price ({{ $currency }})"
                            type="number"
                            step="0.01"
                            name="selling_price"
                            id="selling_price"
                            placeholder="0.00"
                            value="{{ old('selling_price') }}"
                            required
                        />

                        @if($canViewCost)
                            <x-ui.input
                                label="Cost Price ({{ $currency }}) [Optional]"
                                type="number"
                                step="0.01"
                                name="cost_price"
                                id="cost_price"
                                placeholder="0.00"
                                value="{{ old('cost_price') }}"
                            />
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <!-- 2. Stock & Inventory Setup -->
            <x-ui.card title="Stock & Units" description="Initial inventory quantities and stock watch alerts">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-ui.input
                            label="Opening Stock"
                            type="number"
                            step="0.001"
                            name="opening_stock"
                            id="opening_stock"
                            placeholder="0"
                            value="{{ old('opening_stock', '0') }}"
                        />

                        <x-ui.input
                            label="Low Stock Alert Level"
                            type="number"
                            step="0.001"
                            name="low_stock_threshold"
                            id="low_stock_threshold"
                            placeholder="5"
                            value="{{ old('low_stock_threshold', '5') }}"
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
                            selected="{{ old('unit', 'piece') }}"
                        />
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-foreground font-medium">
                            <input
                                type="checkbox"
                                name="track_inventory"
                                value="1"
                                class="rounded border-border text-accent focus:ring-accent"
                                {{ old('track_inventory', '1') ? 'checked' : '' }}
                            />
                            <span>Track inventory for this item (deduct stock automatically on sale)</span>
                        </label>
                    </div>
                </div>
            </x-ui.card>

            <!-- 3. Category & Identifiers (Optional) -->
            <x-ui.card title="Category & Identifiers (Optional)" description="Organize items and add barcode scanning capability">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-ui.select
                            label="Category"
                            name="category_id"
                            id="category_id"
                        >
                            <option value="">Uncategorized</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input
                            label="SKU Code [Optional]"
                            name="sku"
                            id="sku"
                            placeholder="e.g. WTR-001"
                            value="{{ old('sku') }}"
                        />

                        <x-ui.input
                            label="Barcode [Optional]"
                            name="barcode"
                            id="barcode"
                            placeholder="e.g. 8964000123456"
                            value="{{ old('barcode') }}"
                        />
                    </div>

                    <x-ui.textarea
                        label="Product Description [Optional]"
                        name="description"
                        id="description"
                        rows="2"
                        placeholder="Optional details or specifications..."
                        value="{{ old('description') }}"
                    />
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="ghost" size="md" :href="route('app.inventory.index')">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                    Save Product
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
