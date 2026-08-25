# ORVIK Store — Phase 7 Complete Reporting & Export Engine Documentation

## 1. Reporting Architecture
- Centralized reporting hub at `/app/reports` organizing reports into 6 logical business domains: Sales & Revenue, Product Performance, Inventory & Stock, Procurement, Customer Analytics, and Finance/Profitability.
- Multi-tier architecture:
  - **Controllers**: Lean request routing and permission validation (`ReportController`).
  - **Domain Report Services**: Reusable query composition, metric calculations, and pagination (`app/Services/Reports/`).
  - **Export Engine**: Memory-efficient stream downloads for UTF-8 CSV, XML-based Excel workbooks, and printable reports (`ExportService`).
  - **Blade Presentation**: Mobile-first responsive layouts, filter panels, summary cards, and `@media print` stylesheets.

---

## 2. Report Services (`app/Services/Reports/`)
- `SalesReportService.php`: Itemized sales transactions, summary metrics, and interval aggregations (Daily, Weekly, Monthly).
- `ProductReportService.php`: Product-level revenue, COGS, gross margins, ranking, and category performance breakdown.
- `InventoryReportService.php`: Live inventory status, stock valuation (`stock * cost`), stock classifications (In Stock, Low Stock, Out of Stock, Dead Stock, Never Sold), and stock movement audit logs.
- `PurchaseReportService.php`: Procurement spending, supplier invoices, payment tracking (paid vs unpaid), and item drill-downs.
- `ExpenseReportService.php`: Operating expenses, category distribution, payment methods, and overhead trends.
- `CustomerReportService.php`: Customer purchasing frequency, lifetime spend, average order value, and new customer acquisition.
- `SupplierReportService.php`: Supplier order frequency, total procurement value, and outstanding accounts payable.
- `PaymentReportService.php`: Collections distribution across Cash, Card, Bank Transfer, and Mobile Payments.
- `ProfitReportService.php`: P&L Waterfall: Revenue &rarr; COGS &rarr; Gross Profit & Gross Margin % &rarr; Operating Expenses &rarr; Estimated Operating Profit.
- `ExportService.php`: Native streamed downloads for CSV (with UTF-8 BOM) and XML Spreadsheet (.xls).

---

## 3. Report Routes
| Route Name | URI | Description | Permission |
| :--- | :--- | :--- | :--- |
| `app.reports` | `/app/reports` | Report Center Hub | `reports.view` |
| `app.reports.sales` | `/app/reports/sales` | Itemized Sales Transactions | `reports.sales` |
| `app.reports.sales.daily` | `/app/reports/sales/daily` | Daily Sales Summary | `reports.sales` |
| `app.reports.sales.weekly` | `/app/reports/sales/weekly` | Weekly Sales Summary | `reports.sales` |
| `app.reports.sales.monthly` | `/app/reports/sales/monthly` | Monthly Sales Summary | `reports.sales` |
| `app.reports.products` | `/app/reports/products` | Product Sales & Margins | `reports.products` |
| `app.reports.products.categories` | `/app/reports/products/categories` | Category Performance | `reports.products` |
| `app.reports.inventory` | `/app/reports/inventory` | Inventory Status & Valuation | `reports.inventory` |
| `app.reports.inventory.movements` | `/app/reports/inventory/movements` | Stock Movement Audit Trail | `reports.inventory` |
| `app.reports.purchases` | `/app/reports/purchases` | Purchases & Procurement | `reports.purchases` |
| `app.reports.suppliers` | `/app/reports/suppliers` | Supplier Performance | `reports.suppliers` |
| `app.reports.customers` | `/app/reports/customers` | Customer Performance | `reports.customers` |
| `app.reports.expenses` | `/app/reports/expenses` | Operational Expenses | `reports.expenses` |
| `app.reports.payments` | `/app/reports/payments` | Payment Channels | `reports.payments` |
| `app.reports.profit` | `/app/reports/profit` | Profitability & Margins | `reports.profit` |
| `app.reports.export.csv` | `/app/reports/{type}/export/csv` | Streamed CSV Download | Role-dependent |
| `app.reports.export.excel` | `/app/reports/{type}/export/excel` | Streamed Excel Download | Role-dependent |
| `app.reports.print` | `/app/reports/{type}/print` | Printable Report View | Role-dependent |

---

## 4. Report Permissions & Role Matrix
- **`reports.view`**: Access to Report Center.
- **`reports.sales`**: Access to sales transactions, daily/weekly/monthly rollups.
- **`reports.products`**: Access to product sales, units sold, margins.
- **`reports.inventory`**: Access to inventory valuation, stock movements.
- **`reports.purchases`**: Access to procurement bills, purchase costs.
- **`reports.expenses`**: Access to operating expenses.
- **`reports.customers`**: Access to customer reports.
- **`reports.suppliers`**: Access to supplier reports.
- **`reports.payments`**: Access to payment breakdowns.
- **`reports.profit`**: Access to executive profitability & margins.

### Role Mapping:
- **Owner**: Full access to all reports and exports.
- **Manager**: Access to operational and analytical reports.
- **Cashier**: Access limited to Sales, Products, Inventory, Customers, and Payments. **Strictly denied (`403 Forbidden`) from viewing or exporting Profitability, Purchases, and Expenses.**

---

## 5. Sales Reports
- Calculates Gross Sales, Discounts, Taxes, Net Sales, Orders Count, and Average Order Value.
- Excludes voided sales from financial summaries.
- Filterable by payment method, cashier/staff, customer, status, search keyword, and date range.

---

## 6. Inventory Reports
- Live inventory valuation calculated as `SUM(stock_quantity * cost_price)`.
- Explicit status classifications: `IN STOCK`, `LOW STOCK`, `OUT OF STOCK`, `DEAD STOCK` (30+ days without sale), and `NEVER SOLD`.
- Movement audit trail tracking `PURCHASE`, `SALE`, `SALE_RETURN`, `PURCHASE_VOID`, and `ADJUSTMENT`.

---

## 7. Product Reports
- Itemized ranking by units sold, total revenue, COGS, gross profit, and gross margin percentage (`(Profit / Revenue) * 100`).
- Category aggregation showing product count, total units, and category margin contribution.

---

## 8. Purchase Reports
- Invoices ledger tracking PO #, date, supplier, item count, total procurement value, payment status (paid, partial, unpaid), and void status.

---

## 9. Supplier Reports
- Supplier performance tracking order count, total procurement spending, paid totals, and outstanding accounts payable balance.

---

## 10. Customer Reports
- Customer lifetime value (LTV) metrics: total orders completed, total revenue generated, average order value (AOV), and last transaction timestamp.

---

## 11. Expense Reports
- Itemized store overhead costs, categorized into Rent, Utilities, Packaging, Logistics, etc., with payment channel tracking.

---

## 12. Payment Reports
- Distribution of collections across Cash, Card, Bank Transfer, Mobile Payments, and Other, with volume and percentage share.

---

## 13. Profit Reports
- P&L Waterfall with clear disclosure: `"Estimated Operating Profit (Non-GAAP)"`.
- Daily and monthly breakdown timeline comparing revenue vs COGS vs gross profit vs expenses.

---

## 14. Date Filtering & Custom Range System
- Built-in presets: `Today`, `Yesterday`, `This Week`, `This Month`, `Last Month`, `Custom`.
- Start date & end date validation ensuring `start_date <= end_date`.
- All calculations respect the stall's configured timezone (`$business->timezone`).

---

## 15. Search & Filter Implementation
- Real-time search across invoice numbers, customer names, supplier names, product names, and SKUs.
- Filter states persisted in query strings (`?period=this_month&payment_method=cash&search=...`) for bookmarking and refresh reliability.

---

## 16. Print Implementation
- Dedicated `/app/reports/{type}/print` route with `@media print` rules.
- Automatic inclusion of business branding, title, date range, table rows, and page footer while hiding navigation and controls.

---

## 17. CSV Implementation
- Memory-efficient streaming using `response()->streamDownload()`.
- Prepends UTF-8 BOM (`\xEF\xBB\xBF`) ensuring Unicode characters (Urdu, special symbols) open in Excel without corruption.

---

## 18. Excel Implementation
- Standard Microsoft Office XML Spreadsheet format (`.xls`) with explicit cell data types (`Number`, `String`, `Date`), custom font styling, and dark header backgrounds.

---

## 19. PDF Implementation
- Handled through browser-native print-to-PDF via the clean print view (`@media print`), ensuring compatibility with shared hosting without requiring Node.js or Puppeteer.

---

## 20. Audit Logging
- Logs `report.viewed`, `report.exported`, and `report.printed` with `business_id`, `user_id`, report type, format, and timestamp.

---

## 21. Security Testing
- Verified that cashier role receives `403 Forbidden` on profit, purchase, and expense reports as well as their export endpoints.

---

## 22. Tenant Isolation Testing
- Verified that Stall A reports and exports never leak transactions, products, or customers from Stall B.

---

## 23. Financial Reconciliation
- Verified that `Dashboard Sales = Sales Report Net Sales`, `Dashboard Expenses = Expense Report Total`, and `Dashboard COGS = Profit Report COGS`.

---

## 24. Performance Testing
- Database-level aggregations (`SUM`, `COUNT`, `GROUP BY`) with eager loaded relationships and indexed lookups.

---

## 25. IONOS Deployment Requirements
- Standard PHP 8.x + Laravel 11.
- No Node.js background workers, no Redis, no Puppeteer/Chrome server required.

---

## 26. Known Limitations
- Custom date ranges longer than 1 year on high-volume stores should be queried in chunks or exported via CSV.

---

## 27. Recommended Phase 8 Architecture
- **Phase 8: Multi-Stall Management & Central Enterprise Hub** (Multi-location inventory transfers, centralized purchasing, aggregated franchise analytics, and location-based pricing).
