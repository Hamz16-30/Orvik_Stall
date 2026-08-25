<x-layouts.app title="New Supplier" subtitle="Register a new procurement vendor" activeNav="suppliers">
    <div class="max-w-xl space-y-6">
        <a href="{{ route('app.suppliers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Suppliers</span>
        </a>

        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div>
                <h3 class="text-base font-bold text-foreground">Supplier Profile</h3>
                <p class="text-xs text-muted-foreground">Register vendor contact info for procurement and invoices</p>
            </div>

            <form method="POST" action="{{ route('app.suppliers.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Contact Person Name"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="e.g. Tariq Mehmood"
                        required
                        autofocus
                    />

                    <x-ui.input
                        label="Company / Business Name"
                        name="company_name"
                        id="company_name"
                        value="{{ old('company_name') }}"
                        placeholder="e.g. Metro Beverage Wholesale"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Phone Number"
                        name="phone"
                        id="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        placeholder="e.g. 03211234567"
                    />

                    <x-ui.input
                        label="Email Address"
                        name="email"
                        id="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="e.g. sales@metrowholesale.com"
                    />
                </div>

                <x-ui.input
                    label="Address / Warehouse Location"
                    name="address"
                    id="address"
                    value="{{ old('address') }}"
                    placeholder="Warehouse 4, Wholesale Market"
                />

                <div>
                    <label for="notes" class="block text-xs font-semibold text-foreground mb-1.5">Internal Notes</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        placeholder="Payment terms, delivery schedules, representative details..."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" :href="route('app.suppliers.index')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                        Save Supplier
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
