<x-layouts.app title="Edit Customer" subtitle="Update customer contact information" activeNav="customers">
    <div class="max-w-xl space-y-6">
        <a href="{{ route('app.customers.show', $customer->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Profile</span>
        </a>

        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-foreground">Edit {{ $customer->name }}</h3>
                    <p class="text-xs text-muted-foreground">Modify customer details</p>
                </div>

                <form method="POST" action="{{ route('app.customers.destroy', $customer->id) }}" onsubmit="return confirm('Archive this customer? Historical sales will be preserved.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                        Archive Customer
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('app.customers.update', $customer->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input
                    label="Customer Name"
                    name="name"
                    id="name"
                    value="{{ old('name', $customer->name) }}"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Phone Number"
                        name="phone"
                        id="phone"
                        type="tel"
                        value="{{ old('phone', $customer->phone) }}"
                    />

                    <x-ui.input
                        label="Email Address"
                        name="email"
                        id="email"
                        type="email"
                        value="{{ old('email', $customer->email) }}"
                    />
                </div>

                <x-ui.input
                    label="Address"
                    name="address"
                    id="address"
                    value="{{ old('address', $customer->address) }}"
                />

                <div>
                    <label for="status" class="block text-xs font-semibold text-foreground mb-1.5">Status</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-foreground mb-1.5">Internal Notes</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" :href="route('app.customers.show', $customer->id)">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                        Update Customer
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
