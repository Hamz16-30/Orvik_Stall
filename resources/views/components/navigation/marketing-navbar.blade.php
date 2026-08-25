@props([
    'active' => 'home',
])

@php
    $navLinks = config('orvik.marketing_nav', [
        ['label' => 'Home', 'url' => route('home'), 'id' => 'home'],
        ['label' => 'Features', 'url' => route('home') . '#features', 'id' => 'features'],
        ['label' => 'Pricing', 'url' => route('pricing'), 'id' => 'pricing'],
        ['label' => 'About', 'url' => route('home') . '#about', 'id' => 'about'],
        ['label' => 'Contact', 'url' => route('home') . '#contact', 'id' => 'contact'],
    ]);
@endphp

<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 w-full bg-background/85 backdrop-blur-md border-b border-border/50 transition-all">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 md:px-12 lg:px-20">
        <!-- Logo Left -->
        <div class="flex items-center">
            <x-branding.logo size="lg" />
        </div>

        <!-- Desktop Navigation Center/Right -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-muted-foreground" aria-label="Marketing Navigation">
            @foreach($navLinks as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="transition-colors hover:text-foreground {{ ($item['id'] ?? '') === $active ? 'text-foreground font-semibold' : '' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <!-- CTA Right -->
        <div class="hidden md:flex items-center gap-4">
            <a href="{{ route('login') }}" class="text-sm font-medium text-foreground hover:text-accent transition-colors">
                Sign In
            </a>
            <x-ui.button variant="primary" size="md" rounded="full" :href="route('register')">
                Get Started
            </x-ui.button>
        </div>

        <!-- Mobile Hamburger Button -->
        <div class="flex md:hidden items-center gap-2">
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg p-2 text-foreground hover:bg-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                aria-label="Toggle navigation menu"
                :aria-expanded="mobileMenuOpen"
            >
                <i x-show="!mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
                <i x-show="mobileMenuOpen" data-lucide="x" class="w-6 h-6" style="display: none;"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Slide-Down Menu -->
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-b border-border bg-white px-6 py-6 md:hidden shadow-lg"
        style="display: none;"
        @click.outside="mobileMenuOpen = false"
    >
        <div class="flex flex-col space-y-4">
            @foreach($navLinks as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    @click="mobileMenuOpen = false"
                    class="text-base font-medium text-foreground hover:text-accent py-2 border-b border-border/40 transition-colors"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="pt-4 flex flex-col gap-3">
                <a href="{{ route('login') }}" class="w-full text-center py-2.5 text-sm font-medium text-foreground hover:bg-muted rounded-lg transition-colors">
                    Sign In
                </a>
                <x-ui.button variant="primary" size="md" rounded="full" :href="route('app.dashboard')" class="w-full justify-center">
                    Get Started
                </x-ui.button>
            </div>
        </div>
    </div>
</header>
