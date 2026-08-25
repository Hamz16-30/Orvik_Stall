# ORVIK Store — Phase 6 Dashboard, Analytics & Business Intelligence Documentation

## 1. Dashboard Architecture
- Single unified operational dashboard at `/app/dashboard` serving as the post-login landing experience.
- Mobile-first, minimal, and fast UI built with Blade, Tailwind CSS, and Alpine.js.
- Clean separation of concerns: `DashboardController` orchestrates, `DashboardService` aggregates, and `AnalyticsService` handles reusable financial and statistical computations.

---

## 2. KPI Calculations
- **Sales Revenue**: `SUM(sales.total_amount)` for completed non-voided sales within the active date range.
- **Completed Orders**: `COUNT(sales.id)` for completed sales.
- **Gross Profit**: `Revenue - COGS`, where COGS is calculated strictly from historical snapshots (`SUM(sale_items.cost_price * sale_items.quantity)`).
- **Gross Margin**: `(Gross Profit / Revenue) * 100` (safe handling when revenue is 0).
- **Operating Expenses**: `SUM(expenses.amount)` for active non-voided expenses in the period.
- **Estimated Operating Profit**: `Gross Profit - Operating Expenses`.
- **Period Comparisons**: `((Current - Previous) / Previous) * 100` with graceful fallback when prior data is 0 ("+100%" or "No prior period data").

---

## 3. Analytics Services (`AnalyticsService.php` & `DashboardService.php`)
- `AnalyticsService`: Central mathematical and query aggregation engine returning pure structured arrays and collections.
- `DashboardService`: High-level facade assembling all KPI cards, trends, inventory health metrics, attention alerts, and breakdowns.

---

## 4. Date Range System
- Built-in period options: `today` (default), `yesterday`, `this_week`, `this_month`, `last_month`, `custom` (custom start/end picker modal).
- Business Timezone Aware: Resolves day boundaries using `$business->timezone` (e.g. `Asia/Karachi`), ensuring sales near midnight are accurately attributed.
- Automatic Comparison Window: Computes equivalent comparison window (e.g., Today vs Yesterday, This Week vs Last Week, This Month vs Last Month).

---

## 5. Sales Analytics & Trend Engine
- Adaptive time-series grouping:
  - Hourly slots (`00:00`, `03:00`, ..., `21:00`) for `today` and `yesterday`.
  - Daily intervals for `this_week`, `this_month`, `last_month`, and multi-day custom ranges.
- Top Selling Products (Hot Sellers): Grouped by `product_name` and `sku`, ranked primarily by `total_units_sold DESC` and secondarily by `total_revenue DESC`.
- Payment Channel Breakdown: Share percentages and counts for Cash, Card, Mobile Payment, Bank Transfer, and Other.

---

## 6. Inventory Analytics & Health
- **Out of Stock**: `stock_quantity <= 0` with instant 1-tap "Restock" CTA.
- **Low Stock**: `0 < stock_quantity <= low_stock_threshold`.
- **Urgency Sorting**: Out of stock first, followed by lowest remaining stock.

---

## 7. Customer Analytics
- **Total Customers**: Lifetime customer profiles for the tenant.
- **New Customers**: Customers created within the selected date range.
- **Returning Customers**: Customers with `> 1` completed sales.

---

## 8. Supplier & Purchase Analytics
- Total procurement spending and purchase order count for the active period.
- Filtered to non-voided purchases.

---

## 9. Expense Analytics
- Categorized overhead cost distribution (Rent, Utilities, Packaging, Transport, Refreshments).
- Top 5 expense categories with total and percentage share.

---

## 10. Profit Calculations (COGS Integrity)
- Historical sale snapshot costs (`sale_items.cost_price`) are used instead of current product cost price, preserving historical financial integrity even when supplier procurement prices fluctuate.

---

## 11. Dead-Stock & Never-Sold Algorithm
- **Dead Stock**: Active product with `stock_quantity > 0` where latest completed sale was older than configurable threshold (default 30 days).
- **Never-Sold Products**: Active product with `stock_quantity > 0` and 0 lifetime sales.

---

## 12. Low-Stock Algorithm
- Scoped to tracked inventory (`track_inventory = true`) and active status (`status = active`).
- Evaluated against each product's specific `low_stock_threshold`.

---

## 13. Caching Strategy
- Business-scoped cache keys (`orvik:analytics:{business_id}:{period}:{start}_{end}`).
- Cache invalidation hooks on `SaleCreated`, `SaleVoided`, `PurchaseCreated`, `PurchaseVoided`, `ExpenseCreated`, and `ExpenseVoided`.

---

## 14. Database Indexes
- `sales`: `['business_id', 'completed_at']`, `['business_id', 'status']`, `['business_id', 'user_id']`.
- `sale_items`: `sale_id`, `product_id`.
- `products`: `['business_id', 'status']`, `['business_id', 'category_id']`, `['business_id', 'name']`.
- `expenses`: `['business_id', 'expense_date']`, `['business_id', 'category_id']`, `['business_id', 'status']`.
- `purchases`: `['business_id', 'purchased_at']`, `['business_id', 'status']`.
- `customers`: `['business_id', 'status']`, `['business_id', 'phone']`.

---

## 15. Role Permissions & Financial Privacy
- **Owner**: Full access to all financial KPIs, margins, costs, and expenses.
- **Manager**: Business operational dashboard.
- **Cashier**: Operational view only (Sales, Orders, Top Products, Low Stock alerts). Gross Profit, COGS, Purchase Costs, and Operating Expenses are guarded and hidden (`canViewFinancials = false`).

---

## 16. Tenant Isolation
- Every database query strictly filtered by `$business->id`.
- Business A dashboard never aggregates or displays Business B sales or products.

---

## 17. Performance Optimizations
- Database-level aggregations (`SUM`, `COUNT`, `GROUP BY`) instead of in-memory collection loops.
- Eager loading of critical relationships (`with(['saleItems.sale'])`).
- Zero heavy client-side chart library overhead (pure lightweight CSS/SVG chart).

---

## 18. Mobile UI Implementation
- Responsive 2-column mobile layout and horizontal swipeable pills for date ranges.
- Touch-friendly 1-tap quick action bar (POS, Add Product, Add Customer, Record Purchase, Add Expense).
- Floating action buttons and collapsible health tabs (Low, Out, Dead).

---

## 19. Test Results
- **41 PHPUnit Feature Tests with 258 Assertions — 100% Pass Rate**.
- Covered sales metrics, gross profit, margin, operating expenses, period comparison deltas, inventory health classifications, cashier security guards, and tenant isolation.

---

## 20. Known Limitations
- Custom date ranges currently query directly on-demand; future historical archiving can pre-aggregate monthly buckets for multi-year stores.

---

## 21. Recommended Phase 7 Architecture
- **Phase 7: Reports, Exporting & Business Intelligence Export Engine** (Sales reports, inventory valuation reports, expense reports, tax summaries, CSV/Excel exports, and printable PDF audit sheets).
