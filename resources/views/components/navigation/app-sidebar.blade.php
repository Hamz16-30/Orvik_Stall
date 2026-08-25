@props([
    'active' => 'dashboard',
])

@php
    $user = auth()->user();
    $business = $user?->currentBusiness();

    $allNavItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'route' => 'app.dashboard', 'icon' => 'layout-dashboard', 'badge' => null, 'permission' => 'dashboard.view'],
        ['id' => 'sales', 'label' => 'Sales', 'route' => 'app.sales', 'icon' => 'shopping-cart', 'badge' => 'POS', 'badgeVariant' => 'accent', 'permission' => 'sales.view'],
        ['id' => 'inventory', 'label' => 'Inventory', 'route' => 'app.inventory.index', 'icon' => 'boxes', 'badge' => null, 'permission' => 'inventory.view'],
        ['id' => 'customers', 'label' => 'Customers', 'route' => 'app.customers.index', 'icon' => 'users', 'badge' => null, 'permission' => 'customers.view'],
        ['id' => 'suppliers', 'label' => 'Suppliers', 'route' => 'app.suppliers.index', 'icon' => 'truck', 'badge' => null, 'permission' => 'suppliers.view'],
        ['id' => 'purchases', 'label' => 'Purchases', 'route' => 'app.purchases.index', 'icon' => 'package-plus', 'badge' => null, 'permission' => 'purchases.view'],
        ['id' => 'expenses', 'label' => 'Expenses', 'route' => 'app.expenses.index', 'icon' => 'receipt', 'badge' => null, 'permission' => 'expenses.view'],
        ['id' => 'reports', 'label' => 'Reports', 'route' => 'app.reports', 'icon' => 'bar-chart-3', 'badge' => null, 'permission' => 'reports.view'],
        ['id' => 'settings', 'label' => 'Settings', 'route' => 'app.settings', 'icon' => 'settings', 'badge' => null, 'permission' => null],
    ];

    $navItems = array_filter($allNavItems, function ($item) use ($user) {
        if (!$item['permission']) return true;
        return $user && $user->hasPermissionTo($item['permission']);
    });
@endphp

<aside class="hidden lg:flex flex-col w-64 bg-white border-r border-border min-h-screen shrink-0 select-none">
    <!-- Brand Header -->
    <div class="h-16 px-6 flex items-center border-b border-border/70">
        <x-branding.logo size="md" href="{{ route('app.dashboard') }}" />
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto">
        <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
            Stall Operations
        </div>

        @foreach($navItems as $item)
            @php
                $isActive = ($item['id'] === $active);
            @endphp
            <a
                href="{{ route($item['route']) }}"
                class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all group {{ $isActive ? 'bg-secondary text-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:bg-muted/70 hover:text-foreground' }}"
            >
                <div class="flex items-center gap-3">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 transition-transform group-hover:scale-110 {{ $isActive ? 'text-accent' : 'text-muted-foreground group-hover:text-foreground' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </div>

                @if($item['badge'])
                    <x-ui.badge :variant="$item['badgeVariant'] ?? 'secondary'" size="sm" :dot="false">
                        {{ $item['badge'] }}
                    </x-ui.badge>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Store Status Footer -->
    <div class="p-4 border-t border-border/70 bg-muted/20">
        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-border/60 shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <div class="truncate">
                    <p class="text-xs font-semibold text-foreground truncate">{{ $business?->name ?? 'Active Stall' }}</p>
                    <p class="text-[11px] text-muted-foreground">{{ $business?->currency ?? 'PKR' }} &bull; {{ $user?->currentRole()?->display_name ?? 'Operator' }}</p>
                </div>
            </div>
            <a href="{{ route('app.settings') }}" class="p-1 text-muted-foreground hover:text-foreground shrink-0">
                <i data-lucide="settings" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</aside>
