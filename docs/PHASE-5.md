# ORVIK Store — Phase 5 Business Operations Architecture Documentation

## 1. Customers Architecture
- `customers` table with soft-deletes and business scoping (`BelongsToTenant`).
- Lifetime metrics accessors: `total_orders`, `total_spent`, and `last_purchase_date`.
- Quick POS customer creation modal allowing cashiers to register new customers and attach them to active carts on-the-fly without leaving POS.

---

## 2. Suppliers Architecture
- `suppliers` table with soft-deletes and business scoping.
- Tracks vendor profiles, company names, contact numbers, and total procurement spend.
- Deep integration with procurement purchase orders.

---

## 3. Purchases Architecture
- `purchases` and `purchase_items` tables with `decimal(12,2)` financials and `decimal(12,3)` quantities.
- Human-friendly sequential purchase numbers (`PUR-000001`, `PUR-000002`) generated atomically per business.
- Line items preserve snapshot copies of `product_name`, `sku`, `unit_cost`, and `line_total`.
- Printable goods-received voucher receipt at `/app/purchases/{purchase}/receipt`.

---

## 4. Expenses & 5. Expense Categories
- `expense_categories` table with unique business-scoped names.
- `expenses` table for operational overhead tracking (Rent, Electricity, Refreshments, Packaging, Transport).
- Support for active and voided states, ensuring financial records are never hard-deleted.

---

## 6. Database Migrations
- `2026_08_16_000010_create_customers_and_suppliers_tables.php`: `customers`, `suppliers`.
- `2026_08_16_000011_create_purchases_tables.php`: `purchases`, `purchase_items`.
- `2026_08_16_000012_create_expenses_tables.php`: `expense_categories`, `expenses`.

---

## 7. Database Relationships
- `Business` hasMany `Customer`, `Supplier`, `Purchase`, `Expense`, `ExpenseCategory`.
- `Customer` hasMany `Sale` (via `customer_id`).
- `Supplier` hasMany `Purchase`.
- `Purchase` belongsTo `Supplier`, `User`; hasMany `PurchaseItem`.
- `Expense` belongsTo `ExpenseCategory`, `User`.

---

## 8. Inventory Integration & 9. Stock Movement Integration
- Completing a purchase atomically increases product inventory (`Product::lockForUpdate()`), records a `PURCHASE` stock movement (`+quantity`), and updates the product's current `cost_price` to the latest unit cost.
- Voiding a completed purchase safely decreases inventory (`-quantity`) and logs a `PURCHASE_VOID` stock movement.

---

## 10. Permissions & 11. Tenant Isolation
- Permissions seeded: `customers.view`, `customers.manage`, `suppliers.view`, `suppliers.manage`, `purchases.view`, `purchases.create`, `purchases.void`, `expenses.view`, `expenses.create`, `expenses.void`.
- Cashiers are strictly prevented from viewing or creating purchases and expenses (`403 Forbidden`).
- All queries, filters, and validations strictly scoped to the active tenant business.

---

## 12. Audit Logging
- Events recorded: `customer.created`, `customer.updated`, `customer.archived`, `supplier.created`, `supplier.updated`, `supplier.archived`, `purchase.created`, `purchase.voided`, `expense.created`, `expense.updated`, `expense.voided`.

---

## 13. CRUD & 14. Search/Filter Implementation
- Full CRUD for Customers, Suppliers, Purchases, Expenses, and Expense Categories.
- Case-insensitive search by name, phone, email, company name, purchase number, and expense description.
- Filters by date range (Today, Yesterday, This Week, This Month, All), status, supplier, and category.

---

## 15. Mobile UI Implementation
- Responsive card layouts on mobile screens (320px–768px) and compact tables on desktop screens (1024px+).
- Sticky bottom navigation and mobile-first input targets.

---

## 16. Security Testing & 17. Financial Integrity Testing
- 36 automated feature tests with 228 assertions passing at 100%.
- Verified server-side recalculation of purchase totals, discount restrictions, stock movement atomicity, and cross-tenant isolation.

---

## 18. Historical Data Protection
- Archiving a customer or supplier does not break historical sales or purchase receipts (`withTrashed()` relations).
- Snapshot pricing ensures historical sales retain the cost at time of sale, while product cost updates to latest purchase price.

---

## 19. IONOS Shared Hosting Compatibility
- 100% PHP 8.x + Laravel 11 stack with zero daemon or Node.js dependencies.

---

## 20. Known Limitations
- Supplier payment installments and customer credit ledger are deferred to future accounting enhancements.
- Weighted-average costing (FIFO/LIFO) is prepared in architecture but currently uses latest purchase cost strategy.

---

## 21. Recommended Phase 6 Architecture
- **Phase 6: Stall Operations Dashboard & Business Intelligence** (Daily, weekly, monthly charts, profit calculations, top-selling products, dead stock identification, and actionable owner metrics).
