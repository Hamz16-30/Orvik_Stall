@php
    $user = auth()->user();
    $business = $user?->currentBusiness();
    $role = $user?->currentRole();
@endphp

<x-layouts.app title="Dashboard" subtitle="Overview of daily stall performance and live retail metrics" activeNav="dashboard">
    <!-- Top Greeting & Context Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 bg-white p-5 rounded-xl border border-border/80 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h2 class="text-xl font-bold text-foreground tracking-tight">
                    Good day, {{ $user->name }}
                </h2>
                <x-ui.badge variant="accent" size="sm" :dot="false">
                    {{ $role ? $role->display_name : 'Staff' }}
                </x-ui.badge>
            </div>
            <p class="text-xs text-muted-foreground mt-1">
                Active Business Stall: <strong>{{ $business?->name ?? 'ORVIK Stall' }}</strong> &bull; Currency: {{ $business?->currency ?? 'PKR' }} &bull; Timezone: {{ $business?->timezone ?? 'Asia/Karachi' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($user->hasPermissionTo('settings.view'))
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="settings" :href="route('app.settings')">
                    Stall Settings
                </x-ui.button>
            @endif

            @if($user->hasPermissionTo('sales.create'))
                <x-ui.button variant="accent" size="sm" rounded="lg" :href="route('app.sales')" icon="shopping-cart">
                    Open POS Register
                </x-ui.button>
            @endif
        </div>
    </div>

    <!-- Quick Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-dashboard.metric-card
            label="Today's Gross Sales"
            value="{{ ($business?->currency === 'USD' ? '$' : 'Rs. ') }}24,850"
            change="+18.2%"
            trend="up"
            icon="dollar-sign"
        />
        <x-dashboard.metric-card
            label="Total Orders"
            value="128"
            change="+9.5%"
            trend="up"
            icon="shopping-bag"
        />
        <x-dashboard.metric-card
            label="Est. Profit"
            value="{{ ($business?->currency === 'USD' ? '$' : 'Rs. ') }}7,430"
            change="+14.0%"
            trend="up"
            icon="pie-chart"
        />
        <x-dashboard.metric-card
            label="Low Stock Alerts"
            value="8 Items"
            change="Action Needed"
            trend="down"
            icon="alert-triangle"
        />
    </div>

    <!-- Sales Trend Chart + Hot Selling -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <x-dashboard.sales-chart />
        </div>
        <div>
            <x-dashboard.hot-selling-list />
        </div>
    </div>

    <!-- Dead Stock Alert + Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-dashboard.dead-stock-list />
        <x-dashboard.recent-transactions />
    </div>
</x-layouts.app>
