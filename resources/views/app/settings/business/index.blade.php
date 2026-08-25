<x-layouts.app title="Stall & Business Profile" subtitle="Configure core business details, trading currency, and timezone" activeNav="settings">
    <div class="max-w-3xl space-y-6">
        <!-- Back Navigation -->
        <a href="{{ route('app.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Settings</span>
        </a>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <x-ui.card title="Stall Operations Profile" description="Basic retail information printed on receipts and operational exports">
            <form method="POST" action="{{ route('app.settings.business.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input
                    label="Stall / Business Name"
                    name="name"
                    id="name"
                    value="{{ old('name', $business->name) }}"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Store Email"
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $business->email) }}"
                    />

                    <x-ui.input
                        label="Contact Phone"
                        type="text"
                        name="phone"
                        id="phone"
                        value="{{ old('phone', $business->phone) }}"
                    />
                </div>

                <x-ui.textarea
                    label="Stall / Physical Address"
                    name="address"
                    id="address"
                    rows="2"
                    value="{{ old('address', $business->address) }}"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.select
                        label="Default Trading Currency"
                        name="currency"
                        id="currency"
                        :options="['PKR' => 'PKR — Pakistani Rupee (Rs.)', 'USD' => 'USD — US Dollar ($)', 'GBP' => 'GBP — British Pound (£)', 'EUR' => 'EUR — Euro (€)', 'AED' => 'AED — UAE Dirham']"
                        selected="{{ old('currency', $business->currency) }}"
                        required
                    />

                    <x-ui.select
                        label="Business Timezone"
                        name="timezone"
                        id="timezone"
                        :options="[
                            'Asia/Karachi' => 'Asia/Karachi (UTC+5)',
                            'UTC' => 'UTC (Universal Coordinated Time)',
                            'Asia/Dubai' => 'Asia/Dubai (UTC+4)',
                            'Europe/London' => 'Europe/London (GMT/BST)',
                            'America/New_York' => 'America/New_York (EST/EDT)',
                        ]"
                        selected="{{ old('timezone', $business->timezone) }}"
                        required
                    />
                </div>

                <div class="pt-4 border-t border-border flex justify-end">
                    <x-ui.button type="submit" variant="primary" size="md" rounded="lg">
                        Save Business Settings
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
