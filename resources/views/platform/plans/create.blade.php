<x-layouts.platform title="Create Subscription Plan" subtitle="Add a new database-driven pricing tier" activeNav="plans">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
        <form method="POST" action="{{ route('platform.plans.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Plan Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Enterprise" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Slug (optional)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="e.g. enterprise" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-foreground mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Short description of this plan tier..." class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Monthly Price (PKR)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', 0) }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Yearly Price (PKR)</label>
                    <input type="number" step="0.01" name="yearly_price" value="{{ old('yearly_price') }}" placeholder="Optional" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Billing Interval</label>
                    <select name="billing_interval" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', 'PKR') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 text-xs font-medium text-foreground cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-border text-accent focus:ring-accent" />
                    <span>Active (Available for assignment)</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-medium text-foreground cursor-pointer">
                    <input type="checkbox" name="is_public" value="1" checked class="rounded border-border text-accent focus:ring-accent" />
                    <span>Public (Displayed on pricing page)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('platform.plans.index') }}" class="px-4 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-accent text-accent-foreground font-bold text-xs rounded-xl hover:bg-accent/90">
                    Create & Configure Features &rarr;
                </button>
            </div>
        </form>
    </div>
</x-layouts.platform>
