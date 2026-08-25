<x-layouts.app title="Report Center" subtitle="Business reports, financial audits, and multi-format exports" activeNav="reports">
    <div class="space-y-8 max-w-6xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-foreground">Report Center</h1>
                <p class="text-xs text-muted-foreground mt-0.5">Authoritative performance metrics, exportable audit sheets, and operational drill-downs</p>
            </div>
        </div>

        <!-- Category: Sales Reports -->
        @if($user->hasPermissionTo('reports.sales'))
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-1 border-b border-border/60">
                    <i data-lucide="shopping-cart" class="w-4 h-4 text-accent"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Sales & Revenue Reports</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Sales Transactions</h3>
                            <p class="text-xs text-muted-foreground mt-1">Detailed list of completed sales with discounts, taxes, and cashier attribution.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Invoice Ledger</span>
                            <a href="{{ route('app.reports.sales') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Daily Sales Summary</h3>
                            <p class="text-xs text-muted-foreground mt-1">Day-by-day sales revenue, COGS, and gross profit breakdown.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Daily Rollup</span>
                            <a href="{{ route('app.reports.sales.daily') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Weekly Sales Summary</h3>
                            <p class="text-xs text-muted-foreground mt-1">Weekly sales volume and performance trends (Monday to Sunday).</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Weekly Rollup</span>
                            <a href="{{ route('app.reports.sales.weekly') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Monthly Sales Summary</h3>
                            <p class="text-xs text-muted-foreground mt-1">Long-term monthly net sales, gross profits, and operating expenses.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Monthly Rollup</span>
                            <a href="{{ route('app.reports.sales.monthly') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Category: Product Performance Reports -->
        @if($user->hasPermissionTo('reports.products'))
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-1 border-b border-border/60">
                    <i data-lucide="package" class="w-4 h-4 text-accent"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Product Performance Reports</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Product Sales & Margins</h3>
                            <p class="text-xs text-muted-foreground mt-1">Units sold, total revenue, COGS, gross profits, and profit margins per item.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Item Ranking</span>
                            <a href="{{ route('app.reports.products') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Category Performance</h3>
                            <p class="text-xs text-muted-foreground mt-1">Revenue, product diversity, and gross profit breakdown by category.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Category Rollup</span>
                            <a href="{{ route('app.reports.products.categories') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Category: Inventory & Stock Reports -->
        @if($user->hasPermissionTo('reports.inventory'))
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-1 border-b border-border/60">
                    <i data-lucide="boxes" class="w-4 h-4 text-accent"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Inventory & Stock Reports</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Inventory Status & Valuation</h3>
                            <p class="text-xs text-muted-foreground mt-1">Current stock levels, stock thresholds, estimated inventory valuation, and dead stock.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Stock Status</span>
                            <a href="{{ route('app.reports.inventory') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Stock Movement Audit Trail</h3>
                            <p class="text-xs text-muted-foreground mt-1">Chronological history of purchases, sales, adjustments, and inventory reversals.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Movement Logs</span>
                            <a href="{{ route('app.reports.inventory.movements') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Category: Finance & Profitability Reports (Guarded) -->
        @if($user->hasPermissionTo('reports.profit') || $user->hasPermissionTo('reports.expenses'))
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-1 border-b border-border/60">
                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Finance & Profitability Reports</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($user->hasPermissionTo('reports.profit'))
                        <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                            <div>
                                <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Profitability & Margins</h3>
                                <p class="text-xs text-muted-foreground mt-1">Executive overview of Revenue, COGS, Gross Profit, Expenses, and Operating Profit.</p>
                            </div>
                            <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                                <span class="text-[10px] text-emerald-700 font-bold font-mono">Executive Financials</span>
                                <a href="{{ route('app.reports.profit') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                            </div>
                        </div>
                    @endif

                    @if($user->hasPermissionTo('reports.expenses'))
                        <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                            <div>
                                <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Operating Expenses</h3>
                                <p class="text-xs text-muted-foreground mt-1">Itemized store overhead costs, payment channels, and category distribution.</p>
                            </div>
                            <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                                <span class="text-[10px] text-muted-foreground font-mono">Expense Ledger</span>
                                <a href="{{ route('app.reports.expenses') }}" class="text-xs font-bold text-accent hover:underline">View Report &rarr;</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Category: Procurement, Customers & Payments -->
        <div class="space-y-3">
            <div class="flex items-center gap-2 pb-1 border-b border-border/60">
                <i data-lucide="users" class="w-4 h-4 text-accent"></i>
                <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Procurement, Customers & Payments</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($user->hasPermissionTo('reports.purchases'))
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Purchases & Suppliers</h3>
                            <p class="text-xs text-muted-foreground mt-1">Supplier shipment invoices, paid vs unpaid procurement spending, and volume.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Procurement</span>
                            <a href="{{ route('app.reports.purchases') }}" class="text-xs font-bold text-accent hover:underline">View Purchases &rarr;</a>
                        </div>
                    </div>
                @endif

                @if($user->hasPermissionTo('reports.customers'))
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Customer Performance</h3>
                            <p class="text-xs text-muted-foreground mt-1">Customer purchase frequency, lifetime value, and average order size.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Customer LTV</span>
                            <a href="{{ route('app.reports.customers') }}" class="text-xs font-bold text-accent hover:underline">View Customers &rarr;</a>
                        </div>
                    </div>
                @endif

                @if($user->hasPermissionTo('reports.payments'))
                    <div class="bg-white p-5 rounded-2xl border border-border/80 shadow-2xs flex flex-col justify-between space-y-3 hover:border-accent transition-colors group">
                        <div>
                            <h3 class="text-sm font-bold text-foreground group-hover:text-accent">Payment Methods</h3>
                            <p class="text-xs text-muted-foreground mt-1">Distribution across Cash, Cards, Bank Transfers, and Mobile Payments.</p>
                        </div>
                        <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                            <span class="text-[10px] text-muted-foreground font-mono">Payment Channels</span>
                            <a href="{{ route('app.reports.payments') }}" class="text-xs font-bold text-accent hover:underline">View Payments &rarr;</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
