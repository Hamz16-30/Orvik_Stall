<x-layouts.app title="Expense Categories" subtitle="Classify and organize operational overheads" activeNav="expenses">
    <div x-data="{ createModalOpen: false, editModalOpen: false, currentCategory: { id: '', name: '', description: '' } }" class="max-w-4xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.expenses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Expenses</span>
            </a>

            <div class="flex items-center gap-2">
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" @click="createModalOpen = true">
                    Add Category
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <x-ui.card>
            <x-ui.table :headers="['Category Name', 'Description', 'Active Expenses', 'Status', 'Actions']">
                @forelse($categories as $category)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-4 font-bold text-foreground">
                            {{ $category->name }}
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ $category->description ?: '—' }}
                        </td>
                        <td class="px-5 py-4 font-mono font-semibold text-foreground">
                            {{ $category->expenses_count }} expense(s)
                        </td>
                        <td class="px-5 py-4">
                            <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-xs">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="currentCategory = { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', description: '{{ addslashes($category->description ?? '') }}' }; editModalOpen = true;"
                                    class="p-1 text-muted-foreground hover:text-accent"
                                    title="Edit Category"
                                >
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('app.expenses.categories.destroy', $category->id) }}" onsubmit="return confirm('Archive this expense category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-muted-foreground hover:text-rose-600" title="Archive">
                                        <i data-lucide="archive" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-xs text-muted-foreground">
                            No expense categories found. Click "Add Category" to create your first category.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <!-- Create Modal -->
        <div x-show="createModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="createModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h3 class="text-base font-bold text-foreground">New Expense Category</h3>
                        <button type="button" @click="createModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('app.expenses.categories.store') }}" class="space-y-4">
                        @csrf
                        <x-ui.input label="Category Name" name="name" id="new_name" placeholder="e.g. Rent, Utilities, Transport, Staff Food" required />
                        <x-ui.input label="Description" name="description" id="new_desc" placeholder="Brief description of this cost type" />

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="createModalOpen = false">Cancel</x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">Save Category</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="editModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="editModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h3 class="text-base font-bold text-foreground">Edit Category</h3>
                        <button type="button" @click="editModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('/app/expenses/categories') }}/' + currentCategory.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-semibold text-foreground mb-1">Category Name</label>
                            <input type="text" name="name" x-model="currentCategory.name" required class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-foreground mb-1">Description</label>
                            <input type="text" name="description" x-model="currentCategory.description" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                        </div>

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="editModalOpen = false">Cancel</x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">Update Category</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
