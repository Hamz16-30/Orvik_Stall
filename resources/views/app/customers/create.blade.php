<x-layouts.app title="New Customer" subtitle="Add a customer record to your directory" activeNav="customers">
    <div class="max-w-xl space-y-6">
        <a href="{{ route('app.customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Customers</span>
        </a>

        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div>
                <h3 class="text-base font-bold text-foreground">Customer Profile</h3>
                <p class="text-xs text-muted-foreground">Enter primary contact details for receipt issuance and loyalty</p>
            </div>

            <form method="POST" action="{{ route('app.customers.store') }}" class="space-y-4">
                @csrf

                <x-ui.input
                    label="Customer Name"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    placeholder="e.g. Ali Ahmed"
                    required
                    autofocus
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Phone Number"
                        name="phone"
                        id="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        placeholder="e.g. 03001234567"
                    />

                    <x-ui.input
                        label="Email Address"
                        name="email"
                        id="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="e.g. ali@example.com"
                    />
                </div>

                <x-ui.input
                    label="Address"
                    name="address"
                    id="address"
                    value="{{ old('address') }}"
                    placeholder="Street, City, Area"
                />

                <div>
                    <label for="notes" class="block text-xs font-semibold text-foreground mb-1.5">Internal Notes</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        placeholder="Preferences, company name, special discounts..."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" :href="route('app.customers.index')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                        Save Customer
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
