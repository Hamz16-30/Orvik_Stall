@php
    $canManage = $user->hasPermissionTo('inventory.create');
    $canUpdate = $user->hasPermissionTo('inventory.update');
    $canDelete = $user->hasPermissionTo('inventory.delete');
    $canViewCost = $user->isOwner() || $user->isManager();
    $currencySymbol = ($business->currency === 'USD') ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Inventory & Catalog" subtitle="Manage products, stock, and pricing" activeNav="inventory">
    <div class="space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">Catalog & Stock</h2>
                <p class="text-xs text-muted-foreground">Manage stall products, inventory watch thresholds, and operational pricing</p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="folder" :href="route('app.inventory.categories.index')">
                    Categories ({{ $categories->count() }})
                </x-ui.button>

                @if($canManage)
                    <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" :href="route('app.inventory.create')">
                        Add Product
                    </x-ui.button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-dashboard.metric-card
                label="Total Products"
                value="{{ $summary['total_products'] }}"
                change="Catalog Items"
                trend="neutral"
                icon="boxes"
            />

            <x-dashboard.metric-card
                label="Low Stock Alert"
                value="{{ $summary['low_stock_count'] }}"
                change="{{ $summary['low_stock_count'] > 0 ? 'Restock needed' : 'Healthy' }}"
                trend="{{ $summary['low_stock_count'] > 0 ? 'down' : 'up' }}"
                icon="alert-triangle"
            />

            <x-dashboard.metric-card
                label="Out of Stock"
                value="{{ $summary['out_of_stock_count'] }}"
                change="{{ $summary['out_of_stock_count'] > 0 ? 'Action required' : 'All available' }}"
                trend="{{ $summary['out_of_stock_count'] > 0 ? 'down' : 'up' }}"
                icon="package-x"
            />

            @if($canViewCost)
                <x-dashboard.metric-card
                    label="Inventory Value"
                    value="{{ $currencySymbol }}{{ number_format($summary['inventory_value'], 2) }}"
                    change="At Cost Price"
                    trend="neutral"
                    icon="dollar-sign"
                />
            @else
                <x-dashboard.metric-card
                    label="Active Items"
                    value="{{ $summary['total_products'] - $summary['out_of_stock_count'] }}"
                    change="Ready to sell"
                    trend="up"
                    icon="check-circle"
                />
            @endif
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('app.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by product name, SKU, or barcode..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                </div>

                <!-- Category Filter -->
                <div>
                    <select
                        name="category_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status Filter -->
                <div>
                    <select
                        name="stock_status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock Alerts</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Status & Sort Filter -->
                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-1/2 px-2.5 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>

                    <button type="submit" class="px-3 py-2 text-xs font-semibold rounded-lg bg-secondary text-foreground hover:bg-muted transition-colors shrink-0">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'category_id', 'stock_status', 'status', 'sort']))
                        <a href="{{ route('app.inventory.index') }}" class="p-2 text-muted-foreground hover:text-foreground text-xs" title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Desktop Products Table -->
        <div class="hidden md:block">
            <x-ui.card>
                <x-ui.table :headers="['Product Details', 'Category', 'Selling Price', 'Stock Level', 'Status', 'Actions']">
                    @forelse($products as $product)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <!-- Product Name & SKU -->
                            <td class="px-5 py-4">
                                <a href="{{ route('app.inventory.show', $product->id) }}" class="font-bold text-foreground hover:text-accent text-xs block">
                                    {{ $product->name }}
                                </a>
                                <div class="flex items-center gap-2 text-[10px] text-muted-foreground font-mono mt-0.5">
                                    @if($product->sku)
                                        <span>SKU: {{ $product->sku }}</span>
                                    @endif
                                    @if($product->barcode)
                                        <span>&bull; Barcode: {{ $product->barcode }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-4 text-xs text-muted-foreground">
                                {{ $product->category ? $product->category->name : 'Uncategorized' }}
                            </td>

                            <!-- Selling Price -->
                            <td class="px-5 py-4 font-semibold text-foreground text-xs">
                                {{ $currencySymbol }}{{ number_format($product->selling_price, 2) }}
                                @if($canViewCost && $product->cost_price)
                                    <span class="block text-[10px] text-muted-foreground font-normal">Cost: {{ $currencySymbol }}{{ number_format($product->cost_price, 2) }}</span>
                                @endif
                            </td>

                            <!-- Stock Level & Semantic Badge -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-foreground">
                                        {{ (float)$product->stock_quantity }} {{ $product->unit }}
                                    </span>

                                    @if($product->stock_status === \App\Models\Product::STATUS_OUT_OF_STOCK)
                                        <x-ui.badge variant="out-of-stock" size="sm">Out of Stock</x-ui.badge>
                                    @elseif($product->stock_status === \App\Models\Product::STATUS_LOW_STOCK)
                                        <x-ui.badge variant="low-stock" size="sm">Low ({{ (float)$product->low_stock_threshold }})</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="in-stock" size="sm">In Stock</x-ui.badge>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                @if($product->trashed())
                                    <x-ui.badge variant="secondary" size="sm">Archived</x-ui.badge>
                                @else
                                    <x-ui.badge variant="active" size="sm">Active</x-ui.badge>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('app.inventory.show', $product->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="View Details & Stock History">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    @if($canUpdate && !$product->trashed())
                                        <a href="{{ route('app.inventory.edit', $product->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="Edit Details">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if($canDelete)
                                        @if($product->trashed())
                                            <form method="POST" action="{{ route('app.inventory.restore', $product->id) }}">
                                                @csrf
                                                <button type="submit" class="text-xs text-emerald-600 hover:underline font-semibold" title="Restore Product">
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('app.inventory.destroy', $product->id) }}" onsubmit="return confirm('Archive this product? It will no longer appear in POS.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded text-rose-500 hover:text-rose-700" title="Archive Product">
                                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-secondary text-muted-foreground flex items-center justify-center mx-auto">
                                        <i data-lucide="package-search" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-sm font-bold text-foreground">No products found</p>
                                    <p class="text-xs text-muted-foreground">Add your first product to start managing inventory and ring up sales.</p>
                                    @if($canManage)
                                        <div class="pt-2">
                                            <x-ui.button variant="accent" size="sm" rounded="lg" :href="route('app.inventory.create')" icon="plus">
                                                Add Product
                                            </x-ui.button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if($products->hasPages())
                    <div class="px-5 py-4 border-t border-border">
                        {{ $products->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Mobile Products Card List -->
        <div class="md:hidden space-y-3">
            @forelse($products as $product)
                <div class="bg-white p-4 rounded-xl border border-border/80 shadow-2xs space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('app.inventory.show', $product->id) }}" class="font-bold text-foreground text-sm hover:text-accent">
                                {{ $product->name }}
                            </a>
                            <p class="text-[11px] text-muted-foreground">{{ $product->category ? $product->category->name : 'Uncategorized' }}</p>
                        </div>

                        @if($product->stock_status === \App\Models\Product::STATUS_OUT_OF_STOCK)
                            <x-ui.badge variant="out-of-stock" size="sm">Out of Stock</x-ui.badge>
                        @elseif($product->stock_status === \App\Models\Product::STATUS_LOW_STOCK)
                            <x-ui.badge variant="low-stock" size="sm">Low Stock</x-ui.badge>
                        @else
                            <x-ui.badge variant="in-stock" size="sm">In Stock</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-border/50">
                        <div>
                            <span class="text-muted-foreground">Price:</span>
                            <span class="font-bold text-foreground ml-1">{{ $currencySymbol }}{{ number_format($product->selling_price, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Stock:</span>
                            <span class="font-mono font-bold text-foreground ml-1">{{ (float)$product->stock_quantity }} {{ $product->unit }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-border/50 text-xs">
                        <x-ui.button variant="secondary" size="sm" rounded="lg" :href="route('app.inventory.show', $product->id)">
                            Details & History
                        </x-ui.button>
                        @if($canUpdate && !$product->trashed())
                            <x-ui.button variant="secondary" size="sm" rounded="lg" :href="route('app.inventory.edit', $product->id)">
                                Edit
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-border text-center space-y-2">
                    <p class="text-sm font-bold text-foreground">No products found</p>
                    <p class="text-xs text-muted-foreground">Tap below to add your first product.</p>
                    @if($canManage)
                        <div class="pt-2">
                            <x-ui.button variant="accent" size="sm" rounded="lg" :href="route('app.inventory.create')" icon="plus">
                                Add Product
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            @endforelse

            @if($products->hasPages())
                <div class="pt-2">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
