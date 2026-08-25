# ORVIK Store — Phase 3 Inventory & Product Management Architecture Documentation

## 1. Database Migrations
- `database/migrations/2026_08_16_000006_create_categories_table.php`: `categories` table with `business_id`, `name`, `description`, `status`, unique index on `['business_id', 'name']`, and soft deletes.
- `database/migrations/2026_08_16_000007_create_products_table.php`: `products` table with `business_id`, `category_id` (nullable, `nullOnDelete`), `name`, `sku`, `barcode`, `description`, `cost_price` (`decimal(12,2)`), `selling_price` (`decimal(12,2)`), `stock_quantity` (`decimal(12,3)`), `low_stock_threshold` (`decimal(12,3)`), `unit`, `status`, `track_inventory`, composite indexes, and soft deletes.
- `database/migrations/2026_08_16_000008_create_stock_movements_table.php`: `stock_movements` table with `business_id`, `product_id`, `user_id`, `type`, `quantity`, `previous_quantity`, `new_quantity`, `reason`, `reference_type`, `reference_id`, and `created_at`.

---

## 2. Models
- `App\Models\Category`: Uses `BelongsToTenant`, `SoftDeletes`.
- `App\Models\Product`: Uses `BelongsToTenant`, `SoftDeletes`. Provides derived stock status (`IN_STOCK`, `LOW_STOCK`, `OUT_OF_STOCK`) and valuation accessors.
- `App\Models\StockMovement`: Append-only model without `updated_at`.

---

## 3. Relationships
- `Business` hasMany `Product`, `Category`, `StockMovement`.
- `Category` belongsTo `Business`, hasMany `Product`.
- `Product` belongsTo `Business`, belongsTo `Category` (`withTrashed()`), hasMany `StockMovement`.
- `StockMovement` belongsTo `Business`, belongsTo `Product`, belongsTo `User`.

---

## 4. Controllers
- `App\Http\Controllers\InventoryController`: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` (archive), `restore`.
- `App\Http\Controllers\CategoryController`: `index`, `store`, `update`, `destroy` (archive), `restore`.
- `App\Http\Controllers\StockAdjustmentController`: `store` (executes atomic stock adjustment).

---

## 5. Services
- `App\Services\ProductService`: Handles creation (with initial opening stock movement record), update (excluding stock quantity), soft-deletion archiving, and restoration.
- `App\Services\InventoryService`: Centralized single source of truth for stock modifications. Uses `lockForUpdate()` and database transactions, computes real-time summary metrics, and generates tenant-filtered paginated catalog queries.

---

## 6. Policies
- `App\Policies\ProductPolicy`: Enforces permissions for viewing, creating, editing, archiving, restoring, stock adjustment, and cost price visibility.
- `App\Policies\CategoryPolicy`: Enforces category access permissions.

---

## 7. Form Requests
- `CreateProductRequest` & `UpdateProductRequest`: Validates product details and scopes SKU and Barcode uniqueness per business.
- `StockAdjustmentRequest`: Validates adjustment type, positive quantities, and required reason for `SET` adjustments.
- `CreateCategoryRequest` & `UpdateCategoryRequest`: Validates category details and scopes category name uniqueness per business.

---

## 8. Routes
RESTful routes registered under `/app/inventory` and `/app/inventory/categories` with server-side authorization middleware (`permission:inventory.*`).

---

## 9. Inventory UI & 10. Category UI
- Clean, high-contrast, mobile-first design matching Phase 0/1 tokens.
- Desktop data tables + responsive mobile product cards.
- Live summary metrics (Total Products, Low Stock Alerts, Out of Stock, Total Valuation).
- Product details with stock adjustment modal and stock movements timeline.
- Category management hub with modal creation and update.

---

## 11. Stock Adjustment System & 12. Stock Movement System
- Types supported: `ADD`, `REMOVE`, `SET`.
- Negative stock prevention: Attempting to remove more than current stock is rejected with a validation error.
- All modifications generate immutable `StockMovement` records capturing previous quantity, delta, new quantity, operator, and reason.

---

## 13. Search/Filter System & 14. Mobile Implementation
- Fast search by product name, SKU, and barcode.
- Filtering by category, stock status (`in_stock`, `low_stock`, `out_of_stock`), and archive status (`active`, `archived`).
- Mobile-optimized responsive cards with accessible tap targets.

---

## 15. Security & 16. Tenant-Isolation Tests
- 20 feature tests with 110 assertions passing at 100%.
- Verified cross-tenant boundary isolation, cost price privacy for cashiers, and authorization barriers for stock adjustment.

---

## 17. Inventory Calculations
- Inventory valuation computed strictly as `SUM(stock_quantity * cost_price)` for tracked products with recorded cost.
- Low stock computed dynamically from `stock_quantity <= low_stock_threshold`.

---

## 18. Issues Discovered & 19. Technical Decisions Made
- Discovered and addressed route naming consistency in mobile bottom navigation.
- Maintained strict separation: direct editing of `stock_quantity` in `updateProduct` is disallowed; all stock changes route through `InventoryService`.

---

## 20. Phase 4 Confirmation
**Phase 4 (POS, Checkout, Payments, and Sales Transactions) has NOT been started.** The codebase is strictly in Phase 3 state awaiting user instruction.
