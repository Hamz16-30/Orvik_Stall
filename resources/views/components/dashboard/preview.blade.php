<div class="rounded-2xl overflow-hidden p-2 sm:p-4 glass-preview-container select-none">
    <!-- Inner Mock App Window -->
    <div class="rounded-xl overflow-hidden bg-white border border-border/80 shadow-sm flex flex-col">
        <!-- Mock Browser / App Titlebar -->
        <div class="h-10 px-4 bg-secondary/60 border-b border-border/70 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-400/80"></span>
                <span class="w-3 h-3 rounded-full bg-amber-400/80"></span>
                <span class="w-3 h-3 rounded-full bg-emerald-400/80"></span>
                <span class="text-[11px] font-mono text-muted-foreground ml-2 hidden sm:inline">app.orvikstore.com/dashboard</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[11px] font-medium text-muted-foreground">Live POS Sync</span>
            </div>
        </div>

        <div class="flex">
            <!-- Mock Mini Sidebar (Desktop only) -->
            <div class="hidden md:flex flex-col w-48 bg-secondary/30 border-r border-border/70 p-3 space-y-1">
                <div class="flex items-center gap-2 px-2 py-1.5 mb-2">
                    <span class="text-accent text-sm font-bold">&#10022;</span>
                    <span class="text-xs font-bold text-foreground">ORVIK Store</span>
                </div>

                <div class="px-2.5 py-1.5 rounded-lg bg-white border border-border/70 text-xs font-semibold text-foreground flex items-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-accent"></i>
                    <span>Dashboard</span>
                </div>
                <div class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>Sales</span>
                    </div>
                    <span class="text-[9px] bg-accent/10 text-accent font-bold px-1.5 py-0.2 rounded">POS</span>
                </div>
                <div class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                        <span>Inventory</span>
                    </div>
                    <span class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.2 rounded">3 Low</span>
                </div>
                <div class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                    <span>Reports</span>
                </div>
                <div class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground flex items-center gap-2">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Customers</span>
                </div>
            </div>

            <!-- Mock Dashboard Body -->
            <div class="flex-1 p-3 sm:p-5 bg-secondary/15 space-y-4">
                <!-- Top Mini Header -->
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-foreground tracking-tight">Main Stall Performance</h3>
                        <p class="text-[11px] text-muted-foreground">Today: Sunday, August 16, 2026</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" class="text-xs h-8 px-3">
                            New Sale
                        </x-ui.button>
                    </div>
                </div>

                <!-- 4 Metrics Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <x-dashboard.metric-card
                        label="Today's Sales"
                        value="$2,845.50"
                        change="+18.2%"
                        trend="up"
                        icon="dollar-sign"
                    />
                    <x-dashboard.metric-card
                        label="Total Orders"
                        value="64"
                        change="+9.5%"
                        trend="up"
                        icon="shopping-bag"
                    />
                    <x-dashboard.metric-card
                        label="Est. Profit"
                        value="$1,120.00"
                        change="+14.0%"
                        trend="up"
                        icon="pie-chart"
                    />
                    <x-dashboard.metric-card
                        label="Low Stock Alerts"
                        value="3 Items"
                        change="Reorder"
                        trend="down"
                        icon="alert-triangle"
                    />
                </div>

                <!-- Chart + Hot Selling Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                    <div class="lg:col-span-2">
                        <x-dashboard.sales-chart />
                    </div>
                    <div>
                        <x-dashboard.hot-selling-list />
                    </div>
                </div>

                <!-- Dead Stock + Recent Transactions Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <x-dashboard.dead-stock-list />
                    <x-dashboard.recent-transactions />
                </div>
            </div>
        </div>
    </div>
</div>
