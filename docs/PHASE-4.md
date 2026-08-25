# ORVIK Store — Phase 4 POS & Sales Engine Architecture Documentation

## 1. Sales Database Structure
- `sales` table: `id`, `business_id`, `user_id`, `customer_id` (nullable), `invoice_number` (string 50), `subtotal` (`decimal(12,2)`), `discount_amount` (`decimal(12,2)`), `tax_amount` (`decimal(12,2)`), `total_amount` (`decimal(12,2)`), `amount_received` (`decimal(12,2)`, nullable), `change_amount` (`decimal(12,2)`, nullable), `payment_method` (`cash`, `card`, `bank_transfer`, `mobile_payment`, `other`), `payment_status` (`paid`, `partial`, `unpaid`), `status` (`completed`, `voided`, `refunded`), `idempotency_token` (string 64, nullable), `notes` (nullable), `void_reason` (nullable), `voided_by` (nullable), `voided_at` (nullable), `completed_at`, `timestamps`.
- Unique indexes: `['business_id', 'invoice_number']`, `['business_id', 'idempotency_token']`. Composite indexes on `['business_id', 'completed_at']`, `['business_id', 'status']`, and `['business_id', 'user_id']`.

---

## 2. Sale Item Structure
- `sale_items` table: `id`, `sale_id`, `product_id` (nullable, `nullOnDelete`), `product_name`, `sku` (nullable), `quantity` (`decimal(12,3)`), `unit_price` (`decimal(12,2)`), `cost_price` (`decimal(12,2)`, nullable), `discount_amount` (`decimal(12,2)`), `line_total` (`decimal(12,2)`), `timestamps`.
- **Historical Snapshot Protection**: Stores `product_name`, `sku`, `unit_price`, and `cost_price` as immutable transaction snapshots. Future product renaming or price modifications do not alter completed historical receipts.

---

## 3. POS Architecture
- Built as an ultra-fast, single-page cash register interface rendered via Blade and Alpine.js.
- Clean separation between presentation and business logic: Controllers remain thin, routing requests through `SalesService`, `InventoryService`, and `InvoiceService`.
- Reusable product catalog with instant category pills and live client-side search.

---

## 4. Cart Architecture
- Session-backed cart (`sessionStorage`) to survive inadvertent page refreshes without prematurely creating database records.
- 1-tap add to cart directly from product cards.
- Live item adjustments (+, -, remove, clear cart) with stock boundary limits.
- Financial arithmetic is calculated for UI feedback in Alpine.js, but authoritatively recalculated and enforced server-side.

---

## 5. Payment Architecture
- Initial methods supported: `CASH`, `CARD`, `BANK_TRANSFER`, `MOBILE_PAYMENT`, `OTHER`.
- Cash payments include quick cash denomination buttons (+100, +500, +1000, +5000, Exact) and live change calculator.
- Change amount is strictly recalculated server-side (`bcsub($amount_received, $total, 2)`).

---

## 6. Inventory Integration & 7. Stock Deduction Logic
- Integrated with `InventoryService` from Phase 3.
- Deductions execute within an atomic database transaction using `lockForUpdate()`.
- If any product has insufficient stock, the transaction aborts with a validation error before creating records.
- Products with `track_inventory = false` bypass stock validation (supporting non-stock services).

---

## 8. Stock Movement Logic
- Completed sales generate immutable `StockMovement` records:
  - `type`: `SALE`
  - `quantity`: `-sale_quantity`
  - `previous_quantity`, `new_quantity`
  - `reference_type`: `'sale'`
  - `reference_id`: `$sale->id`

---

## 9. Invoice Numbering (`App\Services\InvoiceService`)
- Business-scoped sequential numbers (e.g. `INV-000001`, `INV-000002`).
- Generated atomically per business to ensure concurrency safety and prevent collisions across multiple cashiers.

---

## 10. Receipt System
- Accessible at `/app/sales/{sale}/receipt`.
- Standard 80mm thermal receipt format with stall branding, address, invoice number, cashier name, line items, breakdown, and change.
- Utilizes standard browser printing (`window.print()`) without external daemon dependencies.

---

## 11. Sales History
- Located at `/app/sales/history`.
- Displays daily summary metrics (Today's Total Sales, Today's Orders, Average Order Value).
- Multi-dimensional filters: Search (invoice #, product name), Date Range (Today, Yesterday, This Week, This Month, All), Payment Method, and Status.
- Mobile-optimized responsive card list.

---

## 12. Void / Refund Foundation
- A completed sale is never deleted from the database.
- Voiding updates `status = 'voided'`, captures the operator, timestamp, and required void reason.
- Automatically generates reversing `StockMovement` records (`type = 'SALE_RETURN'`) to return items to inventory.

---

## 13. Authorization
- Enforced via `SalePolicy` and middleware.
- Cashier: Allowed to view POS register, create sales, and view their own sales. Forbidden from voiding sales or viewing sensitive cost prices.
- Owner / Manager: Full sales access including voiding sales and viewing stall-wide transaction history.

---

## 14. Tenant Isolation
- Every query, validation, and route is strictly tenant-scoped via `TenantContext` and `BelongsToTenant`.
- Cross-tenant product sales, receipt views, and void requests are rejected with `403 Forbidden` / `404 Not Found`.

---

## 15. Security Tests & 16. Transaction Tests
- Automated test coverage: 29 feature tests with 166 assertions passing at 100%.
- Verified price tampering rejection (client cannot override database prices), insufficient stock rejection, atomic rollback, and strict tenant isolation.

---

## 17. Duplicate-Submission Protection (Idempotency)
- Unique `idempotency_token` generated per checkout session.
- If a cashier double-clicks "Complete Sale", the server identifies the duplicate token and returns the existing sale without creating duplicate charges or deducting double inventory.

---

## 18. Mobile UI & 19. Desktop UI
- **Desktop**: 2-column layout (65% Product grid + 35% Sticky cart and financial checkout panel).
- **Mobile**: Responsive product grid + persistent bottom action bar ("View Cart — Rs. X") + slide-up drawer bottom sheet for cart and payment selection.

---

## 20. IONOS Shared Hosting Compatibility
- Fully compliant with standard PHP 8.x + Laravel 11.
- No Node.js runtime, Redis, Docker, or printer server daemons required.

---

## 21. Known Limitations
- Tax engine is initialized to 0.00 for standard stall retail; customizable tax brackets can be introduced in settings.
- Barcode scanning works via standard USB/Bluetooth hardware barcode scanners emitting keyboard input into the search bar. Camera scanning is not implemented.

---

## 22. Recommended Next Phase
- **Phase 5: Customer Management & Loyalty Foundation** (Customer CRM, purchase history, quick customer tagging at POS).
