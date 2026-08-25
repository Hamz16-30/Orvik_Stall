@props([
    'active' => 'dashboard',
])

@php
    $user = auth()->user();
    $bottomNav = [
        ['id' => 'dashboard', 'label' => 'Home', 'route' => 'app.dashboard', 'icon' => 'layout-dashboard', 'permission' => 'dashboard.view'],
        ['id' => 'sales', 'label' => 'POS', 'route' => 'app.sales', 'icon' => 'shopping-cart', 'isPrimary' => true, 'permission' => 'sales.view'],
        ['id' => 'inventory', 'label' => 'Stock', 'route' => 'app.inventory.index', 'icon' => 'boxes', 'permission' => 'inventory.view'],
        ['id' => 'reports', 'label' => 'Reports', 'route' => 'app.reports', 'icon' => 'bar-chart-3', 'permission' => 'reports.view'],
        ['id' => 'settings', 'label' => 'Settings', 'route' => 'app.settings', 'icon' => 'settings', 'permission' => null],
    ];

    $items = array_filter($bottomNav, function ($item) use ($user) {
        if (!$item['permission']) return true;
        return $user && $user->hasPermissionTo($item['permission']);
    });
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-border px-2 py-1 safe-bottom shadow-lg" aria-label="Mobile Navigation">
    <div class="flex items-center justify-around max-w-md mx-auto">
        @foreach($items as $item)
            @php
                $isActive = ($item['id'] === $active);
                $isPrimary = $item['isPrimary'] ?? false;
            @endphp

            @if($isPrimary)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex flex-col items-center justify-center -mt-5 group"
                    aria-label="{{ $item['label'] }}"
                >
                    <div class="w-12 h-12 rounded-full bg-accent text-accent-foreground flex items-center justify-center shadow-lg shadow-accent/30 transition-transform active:scale-95 group-hover:scale-105">
                        <i data-lucide="{{ $item['icon'] }}" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-semibold text-accent mt-1">{{ $item['label'] }}</span>
                </a>
            @else
                <a
                    href="{{ route($item['route']) }}"
                    class="flex flex-col items-center justify-center min-h-[48px] min-w-[48px] px-2 py-1 rounded-lg transition-colors {{ $isActive ? 'text-accent font-semibold' : 'text-muted-foreground hover:text-foreground' }}"
                >
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 transition-transform active:scale-90 {{ $isActive ? 'text-accent' : '' }}"></i>
                    <span class="text-[10px] mt-0.5">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
