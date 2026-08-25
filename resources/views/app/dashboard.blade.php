@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Dashboard" subtitle="Operational stall intelligence and live business metrics" activeNav="dashboard">
    <div x-data="{ customRangeModal: false, activeStockTab: 'low_stock' }" class="space-y-6">
        <!-- Top Bar: Greeting & Contextual Date Filter -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $greeting }}</h1>
                <p class="text-xs text-muted-foreground mt-0.5">Here's how {{ $business->name }} is performing for <strong class="text-foreground font-semibold">{{ $dateRange['label'] }}</strong>.</p>
            </div>

            <!-- Date Range Selector Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none bg-white p-1.5 rounded-2xl border border-border/80 shadow-2xs">
                <a
                    href="{{ route('app.dashboard', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none {{ $period === 'today' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    Today
                </a>
                <a
                    href="{{ route('app.dashboard', ['period' => 'yesterday']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none {{ $period === 'yesterday' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    Yesterday
                </a>
                <a
                    href="{{ route('app.dashboard', ['period' => 'this_week']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none {{ $period === 'this_week' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    This Week
                </a>
                <a
                    href="{{ route('app.dashboard', ['period' => 'this_month']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none {{ $period === 'this_month' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    This Month
                </a>
                <a
                    href="{{ route('app.dashboard', ['period' => 'last_month']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none {{ $period === 'last_month' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    Last Month
                </a>
                <button
                    type="button"
                    @click="customRangeModal = true"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 select-none flex items-center gap-1 {{ $period === 'custom' ? 'bg-primary text-white shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-secondary' }}"
                >
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Custom</span>
                </button>
            </div>
        </div>

        <!-- Fast 1-Tap Quick Action Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a
                href="{{ route('app.sales') }}"
                class="px-4 py-2 rounded-xl bg-accent text-white font-bold text-xs flex items-center gap-2 shadow-xs hover:bg-accent/90 shrink-0 transition-transform active:scale-95"
            >
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Open POS Register</span>
            </a>

            <a
                href="{{ route('app.inventory.create') }}"
                class="px-3.5 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shrink-0 transition-colors shadow-2xs"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5 text-muted-foreground"></i>
                <span>Add Product</span>
            </a>

            <a
                href="{{ route('app.customers.create') }}"
                class="px-3.5 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shrink-0 transition-colors shadow-2xs"
            >
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-muted-foreground"></i>
                <span>Add Customer</span>
            </a>

            @if($canViewFinancials)
                <a
                    href="{{ route('app.purchases.create') }}"
                    class="px-3.5 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shrink-0 transition-colors shadow-2xs"
                >
                    <i data-lucide="package-plus" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Record Purchase</span>
                </a>

                <a
                    href="{{ route('app.expenses.create') }}"
                    class="px-3.5 py-2 rounded-xl bg-white border border-border text-foreground font-semibold text-xs flex items-center gap-1.5 hover:bg-secondary shrink-0 transition-colors shadow-2xs"
                >
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    <span>Add Expense</span>
                </a>
            @endif
        </div>

        @if(!$hasAnySales)
            <!-- First-Time Onboarding Welcome Card -->
            <div class="bg-gradient-to-r from-primary/5 via-accent/5 to-primary/5 p-6 rounded-3xl border border-accent/20 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-base font-bold text-foreground">Welcome to ORVIK Store! 🎉</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Let's set up your stall and start ringing up your first customer transactions.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="bg-white p-4 rounded-2xl border border-border space-y-2">
                        <div class="w-7 h-7 rounded-lg bg-secondary flex items-center justify-center font-bold text-xs text-primary">1</div>
                        <h4 class="text-xs font-bold text-foreground">Add Products</h4>
                        <p class="text-[11px] text-muted-foreground">Define your sellable items, cost prices, and selling prices.</p>
                        <a href="{{ route('app.inventory.create') }}" class="text-xs font-bold text-accent hover:underline block pt-1">Add Product &rarr;</a>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-border space-y-2">
                        <div class="w-7 h-7 rounded-lg bg-secondary flex items-center justify-center font-bold text-xs text-primary">2</div>
                        <h4 class="text-xs font-bold text-foreground">Stock Intake</h4>
                        <p class="text-[11px] text-muted-foreground">Set opening stock or record supplier shipment purchases.</p>
                        <a href="{{ route('app.purchases.create') }}" class="text-xs font-bold text-accent hover:underline block pt-1">Record Purchase &rarr;</a>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-border space-y-2">
                        <div class="w-7 h-7 rounded-lg bg-secondary flex items-center justify-center font-bold text-xs text-primary">3</div>
                        <h4 class="text-xs font-bold text-foreground">Ring Up Sales</h4>
                        <p class="text-[11px] text-muted-foreground">Use the 1-tap POS register to checkout customers rapidly.</p>
                        <a href="{{ route('app.sales') }}" class="text-xs font-bold text-accent hover:underline block pt-1">Open POS &rarr;</a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Primary KPI Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $canViewFinancials ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} gap-4">
            <!-- 1. Total Sales -->
            <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-muted-foreground">Total Sales Revenue</span>
                    <div class="w-8 h-8 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold font-mono text-foreground tracking-tight">
                        {{ $currency }}{{ number_format($kpis['revenue'], 2) }}
                    </span>
                </div>
                <div class="text-[11px] flex items-center gap-1.5">
                    @if($kpis['revenue_change'])
                        <span class="font-bold font-mono {{ $kpis['revenue_change']['trend'] === 'up' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $kpis['revenue_change']['formatted'] }}
                        </span>
                        <span class="text-muted-foreground">vs previous period</span>
                    @else
                        <span class="text-muted-foreground">No prior period data</span>
                    @endif
                </div>
            </div>

            <!-- 2. Total Orders -->
            <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-muted-foreground">Completed Orders</span>
                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold font-mono text-foreground tracking-tight">
                        {{ number_format($kpis['orders_count']) }}
                    </span>
                </div>
                <div class="text-[11px] flex items-center gap-1.5">
                    @if($kpis['orders_change'])
                        <span class="font-bold font-mono {{ $kpis['orders_change']['trend'] === 'up' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $kpis['orders_change']['formatted'] }}
                        </span>
                        <span class="text-muted-foreground">vs previous period</span>
                    @else
                        <span class="text-muted-foreground">No prior period data</span>
                    @endif
                </div>
            </div>

            @if($canViewFinancials)
                <!-- 3. Gross Profit -->
                <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-muted-foreground">Gross Profit</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold font-mono text-foreground tracking-tight">
                            {{ $currency }}{{ number_format($kpis['gross_profit'], 2) }}
                        </span>
                    </div>
                    <div class="text-[11px] flex items-center justify-between">
                        <span class="text-emerald-700 font-bold font-mono">{{ $kpis['gross_margin'] }}% Margin</span>
                        @if($kpis['gross_profit_change'])
                            <span class="text-muted-foreground font-mono text-[10px]">{{ $kpis['gross_profit_change']['formatted'] }} vs prev</span>
                        @endif
                    </div>
                </div>

                <!-- 4. Operating Expenses -->
                <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-muted-foreground">Operating Expenses</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold font-mono text-foreground tracking-tight">
                            {{ $currency }}{{ number_format($kpis['expenses'], 2) }}
                        </span>
                    </div>
                    <div class="text-[11px] flex items-center justify-between text-muted-foreground">
                        <span>Est. Net: <strong class="text-foreground font-mono font-bold">{{ $currency }}{{ number_format($kpis['operating_profit'], 2) }}</strong></span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actionable Attention Alerts (If Any) -->
        @if(count($attentionAlerts) > 0)
            <div class="space-y-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground px-1">Needs Your Attention</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($attentionAlerts as $alert)
                        <div class="bg-white p-4 rounded-2xl border border-border/90 shadow-2xs flex flex-col justify-between space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 {{ $alert['level'] === 'critical' ? 'bg-rose-100 text-rose-700' : ($alert['level'] === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700') }}">
                                    <i data-lucide="{{ $alert['icon'] }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-foreground leading-snug">{{ $alert['title'] }}</h4>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">{{ $alert['description'] }}</p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-border/40 flex justify-end">
                                <a href="{{ $alert['action_route'] }}" class="text-xs font-bold text-accent hover:underline">
                                    {{ $alert['action_label'] }} &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Interactive Lightweight Sales Trend Chart -->
        <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-border/60">
                <div>
                    <h3 class="text-sm font-bold text-foreground">Sales Revenue Trend</h3>
                    <p class="text-xs text-muted-foreground">{{ $dateRange['grouping'] === 'hourly' ? 'Hourly revenue intervals for selected day' : 'Daily sales performance across the period' }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[11px] text-muted-foreground block">Period Total</span>
                    <span class="text-base font-extrabold font-mono text-foreground">{{ $currency }}{{ number_format($salesTrend['total_period_sales'], 2) }}</span>
                </div>
            </div>

            @if($salesTrend['total_period_sales'] > 0)
                <!-- SVG Area / Bar Trend Visualization -->
                <div class="space-y-2">
                    <div class="h-48 w-full flex items-end gap-2 pt-6 pb-2 px-2 bg-secondary/15 rounded-2xl border border-border/40 overflow-x-auto">
                        @foreach($salesTrend['labels'] as $idx => $label)
                            @php
                                $val = $salesTrend['values'][$idx] ?? 0;
                                $ord = $salesTrend['orders'][$idx] ?? 0;
                                $heightPct = $salesTrend['max_value'] > 0 ? max(6, round(($val / $salesTrend['max_value']) * 100)) : 6;
                            @endphp
                            <div class="flex-1 min-w-[32px] flex flex-col items-center gap-1 group relative h-full justify-end">
                                <!-- Tooltip -->
                                <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition-opacity bg-foreground text-white text-[10px] font-mono py-1 px-2 rounded-lg pointer-events-none whitespace-nowrap shadow-md z-10">
                                    {{ $label }}: {{ $currency }}{{ number_format($val, 2) }} ({{ $ord }} orders)
                                </div>

                                <!-- Bar / Column -->
                                <div
                                    class="w-full rounded-t-lg transition-all {{ $val > 0 ? 'bg-accent group-hover:bg-accent/80' : 'bg-border/60' }}"
                                    style="height: {{ $heightPct }}%;"
                                ></div>

                                <!-- X Axis Label -->
                                <span class="text-[10px] font-mono text-muted-foreground truncate w-full text-center">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="py-12 text-center text-xs text-muted-foreground space-y-1">
                    <i data-lucide="bar-chart-2" class="w-8 h-8 mx-auto text-muted-foreground/40 mb-2"></i>
                    <p class="font-semibold text-foreground">No sales recorded for {{ $dateRange['label'] }}</p>
                    <p class="text-[11px]">Ring up transactions in the POS to visualize sales trends.</p>
                </div>
            @endif
        </div>

        <!-- 2-Column Analytics Hub -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Top Products & Payment Methods (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Top Selling Products (Hot Sellers) -->
                <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <div>
                            <h3 class="text-sm font-bold text-foreground">Top Selling Products</h3>
                            <p class="text-xs text-muted-foreground">Ranked by volume sold in this period</p>
                        </div>
                        <a href="{{ route('app.inventory.index') }}" class="text-xs font-semibold text-accent hover:underline">
                            View Catalog
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-border text-[11px] font-semibold text-muted-foreground">
                                    <th class="pb-2">Product Name</th>
                                    <th class="pb-2 text-right">Units Sold</th>
                                    <th class="pb-2 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @forelse($topProducts as $top)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="py-3 font-semibold text-foreground">
                                            <span>{{ $top->product_name }}</span>
                                            @if($top->sku)
                                                <span class="text-[10px] text-muted-foreground font-mono block">{{ $top->sku }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 font-mono font-bold text-foreground text-right">
                                            {{ (float)$top->total_units_sold }}
                                        </td>
                                        <td class="py-3 font-mono font-extrabold text-foreground text-right">
                                            {{ $currency }}{{ number_format($top->total_revenue, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-xs text-muted-foreground">
                                            No products sold during this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sales by Payment Method -->
                <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-4">
                    <div class="pb-3 border-b border-border/60">
                        <h3 class="text-sm font-bold text-foreground">Sales by Payment Channel</h3>
                        <p class="text-xs text-muted-foreground">Revenue distribution across payment methods</p>
                    </div>

                    @if(count($paymentBreakdown) > 0)
                        <div class="space-y-3">
                            @foreach($paymentBreakdown as $pay)
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-semibold text-foreground">{{ $pay['label'] }} ({{ $pay['count'] }} orders)</span>
                                        <span class="font-mono font-bold text-foreground">{{ $currency }}{{ number_format($pay['total'], 2) }} <span class="text-muted-foreground font-normal">({{ $pay['percentage'] }}%)</span></span>
                                    </div>
                                    <div class="w-full h-2 bg-secondary rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full" style="width: {{ $pay['percentage'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-4 text-center text-xs text-muted-foreground">No completed sales recorded in this period.</p>
                    @endif
                </div>
            </div>

            <!-- Right Column: Inventory Health, Expenses & Customers (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Inventory Health Tabs -->
                <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-4">
                    <div class="pb-2 border-b border-border/60">
                        <h3 class="text-sm font-bold text-foreground">Inventory Health</h3>
                        <p class="text-xs text-muted-foreground">Critical stock thresholds and movement alerts</p>
                    </div>

                    <!-- Tabs Switcher -->
                    <div class="flex items-center gap-1 bg-secondary/80 p-1 rounded-xl text-xs font-semibold">
                        <button
                            type="button"
                            @click="activeStockTab = 'low_stock'"
                            :class="activeStockTab === 'low_stock' ? 'bg-white text-foreground shadow-2xs font-bold' : 'text-muted-foreground'"
                            class="flex-1 py-1.5 text-center rounded-lg transition-all"
                        >
                            Low ({{ $inventoryHealth['low_stock_count'] }})
                        </button>
                        <button
                            type="button"
                            @click="activeStockTab = 'out_of_stock'"
                            :class="activeStockTab === 'out_of_stock' ? 'bg-white text-rose-700 shadow-2xs font-bold' : 'text-muted-foreground'"
                            class="flex-1 py-1.5 text-center rounded-lg transition-all"
                        >
                            Out ({{ $inventoryHealth['out_of_stock_count'] }})
                        </button>
                        <button
                            type="button"
                            @click="activeStockTab = 'dead_stock'"
                            :class="activeStockTab === 'dead_stock' ? 'bg-white text-foreground shadow-2xs font-bold' : 'text-muted-foreground'"
                            class="flex-1 py-1.5 text-center rounded-lg transition-all"
                        >
                            Dead ({{ $inventoryHealth['dead_stock_count'] }})
                        </button>
                    </div>

                    <!-- Low Stock Tab Content -->
                    <div x-show="activeStockTab === 'low_stock'" class="space-y-2">
                        @forelse($inventoryHealth['low_stock']->take(4) as $item)
                            <div class="p-2.5 rounded-xl bg-amber-50/50 border border-amber-200/60 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-foreground">{{ $item->name }}</p>
                                    <p class="text-[10px] text-amber-800 font-mono">Threshold: {{ (float)$item->low_stock_threshold }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded font-mono font-bold bg-amber-200 text-amber-900 text-xs">
                                    {{ (float)$item->stock_quantity }} {{ $item->unit }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-muted-foreground">All items have healthy stock levels.</p>
                        @endforelse
                    </div>

                    <!-- Out of Stock Tab Content -->
                    <div x-show="activeStockTab === 'out_of_stock'" class="space-y-2">
                        @forelse($inventoryHealth['out_of_stock']->take(4) as $item)
                            <div class="p-2.5 rounded-xl bg-rose-50/50 border border-rose-200/60 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-foreground">{{ $item->name }}</p>
                                    <p class="text-[10px] text-rose-700">0 remaining in stock</p>
                                </div>
                                <a href="{{ route('app.purchases.create') }}" class="px-2.5 py-1 rounded bg-rose-600 text-white font-bold text-[11px] hover:bg-rose-700">
                                    Restock
                                </a>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-muted-foreground">No products are out of stock.</p>
                        @endforelse
                    </div>

                    <!-- Dead Stock Tab Content -->
                    <div x-show="activeStockTab === 'dead_stock'" class="space-y-2">
                        @forelse($inventoryHealth['dead_stock']->take(4) as $item)
                            <div class="p-2.5 rounded-xl bg-secondary/50 border border-border/60 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-foreground">{{ $item['product']->name }}</p>
                                    <p class="text-[10px] text-muted-foreground">Last sold: {{ $item['last_sold_date'] }} ({{ $item['days_since_sale'] }}d ago)</p>
                                </div>
                                <span class="font-mono font-bold text-muted-foreground text-xs">
                                    {{ $item['stock'] }} {{ $item['unit'] }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-muted-foreground">No dead stock detected (all items sold within 30 days).</p>
                        @endforelse
                    </div>
                </div>

                @if($canViewFinancials && count($expenseBreakdown) > 0)
                    <!-- Operating Expense Breakdown -->
                    <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-border/60">
                            <div>
                                <h3 class="text-sm font-bold text-foreground">Expense Categories</h3>
                                <p class="text-xs text-muted-foreground">Top overhead cost drivers</p>
                            </div>
                            <a href="{{ route('app.expenses.index') }}" class="text-xs font-semibold text-accent hover:underline">
                                Details
                            </a>
                        </div>

                        <div class="space-y-2.5 text-xs">
                            @foreach($expenseBreakdown as $exp)
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-foreground">{{ $exp['category'] }}</span>
                                    <span class="font-mono font-bold text-foreground">{{ $currency }}{{ number_format($exp['total'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Customer Metrics -->
                <div class="bg-white p-6 rounded-3xl border border-border/80 shadow-2xs space-y-3">
                    <div class="pb-2 border-b border-border/60">
                        <h3 class="text-sm font-bold text-foreground">Customer Activity</h3>
                        <p class="text-xs text-muted-foreground">Retention and contact base</p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-3 bg-secondary/40 rounded-2xl">
                            <span class="text-base font-extrabold font-mono text-foreground">{{ $customerMetrics['total_customers'] }}</span>
                            <span class="text-[10px] text-muted-foreground block mt-0.5">Total</span>
                        </div>
                        <div class="p-3 bg-secondary/40 rounded-2xl">
                            <span class="text-base font-extrabold font-mono text-emerald-700">+{{ $customerMetrics['new_customers'] }}</span>
                            <span class="text-[10px] text-muted-foreground block mt-0.5">New in Period</span>
                        </div>
                        <div class="p-3 bg-secondary/40 rounded-2xl">
                            <span class="text-base font-extrabold font-mono text-accent">{{ $customerMetrics['returning_customers'] }}</span>
                            <span class="text-[10px] text-muted-foreground block mt-0.5">Returning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Modal -->
        <div x-show="customRangeModal" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="customRangeModal = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h3 class="text-base font-bold text-foreground">Select Custom Date Range</h3>
                        <button type="button" @click="customRangeModal = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="GET" action="{{ route('app.dashboard') }}" class="space-y-4">
                        <input type="hidden" name="period" value="custom">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="start_date" class="block text-xs font-semibold text-foreground mb-1">Start Date</label>
                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                                />
                            </div>

                            <div>
                                <label for="end_date" class="block text-xs font-semibold text-foreground mb-1">End Date</label>
                                <input
                                    type="date"
                                    name="end_date"
                                    id="end_date"
                                    value="{{ request('end_date', now()->toDateString()) }}"
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                                />
                            </div>
                        </div>

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="customRangeModal = false">Cancel</x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">Apply Filter</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
