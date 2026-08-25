@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@php
    $user = auth()->user();
    $business = $user?->currentBusiness();
    $role = $user?->currentRole();
@endphp

<header class="h-16 border-b border-border bg-white px-4 md:px-8 flex items-center justify-between gap-4 sticky top-0 z-30">
    <!-- Title / Breadcrumb / Stall context -->
    <div class="flex items-center gap-3">
        <!-- Mobile Logo Icon -->
        <div class="lg:hidden">
            <x-branding.logo size="sm" :showText="false" />
        </div>

        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold tracking-tight text-foreground">{{ $title }}</h1>
                @if($business)
                    <span class="hidden sm:inline-flex text-[11px] px-2 py-0.5 rounded-full bg-secondary border border-border text-foreground font-semibold">
                        {{ $business->name }}
                    </span>
                @endif
            </div>
            @if($subtitle)
                <p class="text-xs text-muted-foreground hidden sm:block">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3">
        @if($user && $user->hasPermissionTo('sales.create'))
            <!-- Quick POS Button -->
            <x-ui.button variant="accent" size="sm" rounded="lg" :href="route('app.sales')" icon="shopping-cart">
                <span class="hidden sm:inline">New Sale</span>
            </x-ui.button>
        @endif

        <!-- User Menu Dropdown -->
        <x-ui.dropdown align="right" width="56">
            <x-slot name="trigger">
                <button type="button" class="flex items-center gap-2.5 rounded-lg p-1.5 hover:bg-muted transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground font-bold flex items-center justify-center text-xs">
                        {{ $user ? strtoupper(substr($user->name, 0, 2)) : 'OS' }}
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-xs font-semibold text-foreground">{{ $user?->name ?? 'Operator' }}</p>
                        <p class="text-[10px] text-muted-foreground">{{ $role?->display_name ?? 'Staff' }}</p>
                    </div>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-muted-foreground"></i>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-2.5 border-b border-border/70 text-xs">
                    <p class="font-semibold text-foreground">{{ $user?->name }}</p>
                    <p class="text-muted-foreground truncate">{{ $user?->email }}</p>
                    @if($business)
                        <p class="text-[10px] text-accent font-semibold mt-1">Stall: {{ $business->name }}</p>
                    @endif
                </div>

                <a href="{{ route('app.settings.account') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-foreground hover:bg-muted transition-colors">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Profile & Password</span>
                </a>

                @if($user && $user->hasPermissionTo('settings.view'))
                    <a href="{{ route('app.settings.business') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-foreground hover:bg-muted transition-colors">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-muted-foreground"></i>
                        <span>Business Settings</span>
                    </a>
                @endif

                <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-foreground hover:bg-muted transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Marketing Page</span>
                </a>

                <div class="border-t border-border/70 my-1"></div>

                <!-- Secure Logout Form -->
                <form method="POST" action="{{ route('app.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            </x-slot>
        </x-ui.dropdown>
    </div>
</header>
