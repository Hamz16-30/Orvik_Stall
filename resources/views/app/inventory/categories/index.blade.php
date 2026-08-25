@php
    $canManage = $user->hasPermissionTo('inventory.create');
    $canUpdate = $user->hasPermissionTo('inventory.update');
    $canDelete = $user->hasPermissionTo('inventory.delete');
@endphp

<x-layouts.app title="Product Categories" subtitle="Organize your stall catalog into retail categories" activeNav="inventory">
    <div x-data="{ createModalOpen: false, editModalOpen: false, editId: null, editName: '', editDesc: '' }" class="max-w-4xl space-y-6">
        <!-- Top Navigation & Action Buttons -->
        <div class="flex items-center justify-between">
            <a href="{{ route('app.inventory.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Inventory</span>
            </a>

            @if($canManage)
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" @click="createModalOpen = true">
                    New Category
                </x-ui.button>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-medium text-rose-800 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <x-ui.card title="Stall Categories ({{ $categories->count() }})" description="Categories group items for fast POS browsing and sales reports">
            <x-ui.table :headers="['Category Name', 'Description', 'Active Products', 'Status', 'Actions']">
                @forelse($categories as $category)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-4 font-bold text-foreground">
                            {{ $category->name }}
                        </td>

                        <td class="px-5 py-4 text-muted-foreground max-w-xs truncate">
                            {{ $category->description ?: '—' }}
                        </td>

                        <td class="px-5 py-4 font-mono font-semibold text-foreground">
                            {{ $category->products_count }} item(s)
                        </td>

                        <td class="px-5 py-4">
                            <x-ui.badge variant="active" size="sm">Active</x-ui.badge>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($canUpdate)
                                    <button
                                        type="button"
                                        @click="editId = {{ $category->id }}; editName = '{{ addslashes($category->name) }}'; editDesc = '{{ addslashes($category->description ?? '') }}'; editModalOpen = true"
                                        class="p-1 text-muted-foreground hover:text-accent rounded"
                                        title="Edit Category"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form method="POST" action="{{ route('app.inventory.categories.destroy', $category->id) }}" onsubmit="return confirm('Archive this category? Associated products will remain in catalog as uncategorized.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 rounded" title="Archive Category">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-xs text-muted-foreground">
                            No categories created yet. Tap <strong>New Category</strong> above to organize your catalog.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <!-- Create Category Modal -->
        <div x-show="createModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="createModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <h3 class="text-base font-bold text-foreground">Create Category</h3>
                        <button type="button" @click="createModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('app.inventory.categories.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <x-ui.input
                            label="Category Name"
                            name="name"
                            id="cat_name"
                            placeholder="e.g. Cold Drinks, Snacks, Biscuits..."
                            required
                        />

                        <x-ui.textarea
                            label="Description [Optional]"
                            name="description"
                            id="cat_desc"
                            rows="2"
                            placeholder="Optional notes or department details..."
                        />

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="createModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">
                                Save Category
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div x-show="editModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="editModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <h3 class="text-base font-bold text-foreground">Edit Category</h3>
                        <button type="button" @click="editModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('app/inventory/categories') }}/' + editId" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <x-ui.input
                            label="Category Name"
                            name="name"
                            id="edit_cat_name"
                            x-model="editName"
                            required
                        />

                        <x-ui.textarea
                            label="Description [Optional]"
                            name="description"
                            id="edit_cat_desc"
                            rows="2"
                            x-model="editDesc"
                        />

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="editModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="primary" size="sm" rounded="lg">
                                Update Category
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
