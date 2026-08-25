<section id="showcase" class="py-20 md:py-28 bg-white border-b border-border/60">
    <div class="mx-auto max-w-7xl px-6 md:px-12 lg:px-20">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-semibold uppercase tracking-wider text-accent">Product Experience</span>
            <h2 class="mt-2 text-3xl sm:text-4xl md:text-5xl font-display font-normal text-foreground tracking-tight">
                Everything at a glance.
            </h2>
            <p class="mt-4 text-base text-muted-foreground leading-relaxed">
                See your sales, inventory, and stock movement without digging through spreadsheets or complex accounting menus.
            </p>
        </div>

        <!-- Interactive Tabs System (Alpine.js) -->
        <div x-data="{ currentTab: 'pos' }" class="space-y-6">
            <!-- Tabs Controls -->
            <div class="flex items-center justify-center">
                <div class="inline-flex p-1.5 rounded-full bg-secondary border border-border gap-1 overflow-x-auto max-w-full">
                    <button
                        type="button"
                        @click="currentTab = 'pos'"
                        :class="currentTab === 'pos' ? 'bg-white text-foreground shadow-xs font-bold' : 'text-muted-foreground hover:text-foreground font-medium'"
                        class="px-5 py-2 rounded-full text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                    >
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>1-Tap Sales (POS)</span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'dashboard'"
                        :class="currentTab === 'dashboard' ? 'bg-white text-foreground shadow-xs font-bold' : 'text-muted-foreground hover:text-foreground font-medium'"
                        class="px-5 py-2 rounded-full text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                    >
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        <span>Live Dashboard</span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'inventory'"
                        :class="currentTab === 'inventory' ? 'bg-white text-foreground shadow-xs font-bold' : 'text-muted-foreground hover:text-foreground font-medium'"
                        class="px-5 py-2 rounded-full text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                    >
                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                        <span>Inventory & Alerts</span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'reports'"
                        :class="currentTab === 'reports' ? 'bg-white text-foreground shadow-xs font-bold' : 'text-muted-foreground hover:text-foreground font-medium'"
                        class="px-5 py-2 rounded-full text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                    >
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        <span>Reports & Profit</span>
                    </button>
                </div>
            </div>

            <!-- Tab Content Containers -->
            <div class="max-w-5xl mx-auto mt-8">
                <!-- POS Tab -->
                <div x-show="currentTab === 'pos'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="mb-4 text-center">
                        <h3 class="text-xl font-bold text-foreground">Make every sale simple.</h3>
                        <p class="text-xs text-muted-foreground mt-1">Find a product, add it to the order, collect payment, and move on in under 5 seconds.</p>
                    </div>
                    @include('landing.components.sales-preview')
                </div>

                <!-- Dashboard Tab -->
                <div x-show="currentTab === 'dashboard'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <div class="mb-4 text-center">
                        <h3 class="text-xl font-bold text-foreground">Complete stall overview in real time.</h3>
                        <p class="text-xs text-muted-foreground mt-1">Monitor revenue, customer orders, and idle stock without leaving the front desk.</p>
                    </div>
                    <x-dashboard.preview />
                </div>

                <!-- Inventory Tab -->
                <div x-show="currentTab === 'inventory'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <div class="mb-4 text-center">
                        <h3 class="text-xl font-bold text-foreground">Proactive inventory intelligence.</h3>
                        <p class="text-xs text-muted-foreground mt-1">Automatic low-stock warnings and capital lockup alerts for slow-moving products.</p>
                    </div>
                    @include('landing.components.inventory-preview')
                </div>

                <!-- Reports Tab -->
                <div x-show="currentTab === 'reports'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <div class="mb-4 text-center">
                        <h3 class="text-xl font-bold text-foreground">Clear business insights for owners.</h3>
                        <p class="text-xs text-muted-foreground mt-1">Understand your exact profit margins, daily revenue, and tax obligations instantly.</p>
                    </div>
                    @include('landing.components.reports-preview')
                </div>
            </div>
        </div>
    </div>
</section>
