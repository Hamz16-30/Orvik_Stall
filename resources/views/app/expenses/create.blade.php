@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Record Expense" subtitle="Log operational store overhead cost" activeNav="expenses">
    <div class="max-w-xl space-y-6">
        <a href="{{ route('app.expenses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Expenses</span>
        </a>

        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div>
                <h3 class="text-base font-bold text-foreground">Operational Expense</h3>
                <p class="text-xs text-muted-foreground">Record overhead payment (Rent, Electricity, Tea/Refreshments, Packaging)</p>
            </div>

            <form method="POST" action="{{ route('app.expenses.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-xs font-semibold text-foreground mb-1.5">Expense Category</label>
                        <select
                            name="category_id"
                            id="category_id"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                            <option value="">Uncategorized</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="expense_date" class="block text-xs font-semibold text-foreground mb-1.5">Expense Date</label>
                        <input
                            type="date"
                            name="expense_date"
                            id="expense_date"
                            value="{{ old('expense_date', now()->toDateString()) }}"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>
                </div>

                <x-ui.input
                    label="Description"
                    name="description"
                    id="description"
                    value="{{ old('description') }}"
                    placeholder="e.g. Shop Electricity Bill - August"
                    required
                    autofocus
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Amount ({{ $currency }})"
                        name="amount"
                        id="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        required
                    />

                    <div>
                        <label for="payment_method" class="block text-xs font-semibold text-foreground mb-1.5">Payment Method</label>
                        <select
                            name="payment_method"
                            id="payment_method"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="mobile_payment">Mobile Payment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <x-ui.input
                    label="Bill / Voucher / Receipt Reference"
                    name="reference"
                    id="reference"
                    value="{{ old('reference') }}"
                    placeholder="e.g. Bill #8849201"
                />

                <div>
                    <label for="notes" class="block text-xs font-semibold text-foreground mb-1.5">Internal Notes</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="2"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" :href="route('app.expenses.index')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="accent" size="md" rounded="lg">
                        Save Expense
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
