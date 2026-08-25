# ORVIK STORE — PHASE 9: SAAS PLATFORM, MONETIZATION & PLAN MANAGEMENT

## 1. Executive Summary
Phase 9 transforms ORVIK Store from standalone stall management software into a complete multi-tenant Commercial SaaS Platform. It introduces a two-layer administrative hierarchy separating tenant business operations (`/app/*`) from global platform governance (`/platform/*`), database-driven subscription tiers (`Free`, `Starter`, `Growth`, `Pro`), strict server-side resource limit enforcement, usage threshold monitoring, automated trial provisioning, payment provider abstraction, idempotency-guaranteed webhooks, and comprehensive SaaS analytics (MRR, ARR, ARPU, conversion, churn).

---

## 2. Two-Layer Administrative Architecture

```
                                  ORVIK PLATFORM ARCHITECTURE
                                              │
                    ┌─────────────────────────┴─────────────────────────┐
                    ▼                                                   ▼
       LAYER 1: BUSINESS TENANCY                               LAYER 2: PLATFORM ADMIN
           Route: `/app/*`                                         Route: `/platform/*`
   Auth: `auth`, `business`, `active.account`                  Auth: `auth`, `platform.admin`
                    │                                                   │
     ┌──────────────┴──────────────┐                     ┌──────────────┴──────────────┐
     ▼                             ▼                     ▼                             ▼
Stall Operations            Tenant Settings        Platform Overview             SaaS Governance
• POS Sales Engine          • Account Profile      • Global KPIs (MRR/ARR/ARPU)  • Plan & Tier Editor
• Inventory & Stock         • Business Metadata    • Multi-Tenant Directory      • Feature Dictionary
• Customers & Suppliers     • Staff & Roles        • Business Health & Status    • Account Suspension
• Purchases & Expenses      • Plan & Quota Usage   • Subscription Transitions    • Platform Settings
• Financial Reports         • Billing & Invoices   • Conversion & Churn          • Immutable SaaS Audit
```

---

## 3. Database-Driven Plans & Feature Dictionary

All subscription tiers and limits are dynamically managed in MySQL / MariaDB via Eloquent models without requiring hardcoded application redeployments.

### Tier Configurations Seeded

| Feature Slug | Feature Name | Type | Free | Starter | Growth | Pro |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `products` | Products Limit | LIMIT | 50 | 500 | 2,500 | Unlimited ($\infty$) |
| `users` | Staff Users Limit | LIMIT | 1 | 3 | 10 | Unlimited ($\infty$) |
| `monthly_sales`| Monthly Sales Quota | QUOTA | 100 | 1,000 | 5,000 | Unlimited ($\infty$) |
| `customers` | Customers Limit | LIMIT | 50 | 500 | 2,500 | Unlimited ($\infty$) |
| `suppliers` | Suppliers Limit | LIMIT | 10 | 50 | 200 | Unlimited ($\infty$) |
| `excel_export` | Excel (.xls) Exports | BOOLEAN | &cross; | &check; | &check; | &check; |
| `pdf_export` | PDF Clean Prints | BOOLEAN | &check; | &check; | &check; | &check; |
| `advanced_reports`| P&L & Profitability Waterfall | BOOLEAN | &cross; | &cross; | &check; | &check; |
| `advanced_analytics`| BI Heatmaps & Peak Hour Trends| BOOLEAN | &cross; | &cross; | &check; | &check; |
| **Pricing** | Monthly / Yearly Price (PKR) | — | **Rs. 0** | **Rs. 1,999 / 19,990** | **Rs. 4,999 / 49,990** | **Rs. 9,999 / 99,990** |

---

## 4. Server-Side Limit & Quota Enforcement

Resource limits are strictly validated server-side within the core domain services:
- **`ProductService::createProduct`**: Validates `canCreateProduct()` prior to inserting new inventory records. Exceeding plan limits throws a `ValidationException` with a clear user prompt.
- **`InvitationController::store`**: Validates staff user count against active plan limits before sending team invites.
- **`SalesService::completeSale`**: Validates monthly transaction volume against `canCompleteSale()`.

### Safe Downgrade & Data Retention Guarantee
When a business downgrades or cancels their subscription:
1. **Zero Data Deletion**: Existing products, customers, transactions, and suppliers remain 100% intact and visible in read-only capacity.
2. **Creation Blocking**: New resource creation is halted until resource counts are brought below the lower tier's ceiling.

---

## 5. Usage Calculation & Proactive Warning Thresholds

The `UsageService` classifies resource usage into four standardized health states:
- **`< 80% (Normal)`**: Default status styling.
- **`80% – 89% (Warning)`**: Amber visual indicator alerting the stall owner that capacity is approaching.
- **`90% – 99% (Critical)`**: High-contrast warning advising immediate upgrade.
- **`100% (Reached)`**: Limit reached indicator; blocks new creations with an actionable upgrade modal.

---

## 6. Payment Provider Abstraction & Webhook Security

The `BillingService` provides a decoupled architecture supporting multiple payment gateways (`ManualBillingProvider`, `TestBillingProvider`, Stripe, JazzCash, EasyPaisa):
- **Signature Verification**: HMAC-SHA256 signature verification guards against spoofing.
- **Idempotency Guard**: All inbound webhook events check `BillingEvent` unique event IDs before processing, preventing double-billing or duplicated subscription renewals.

---

## 7. Verification & Automated Test Results

The entire ORVIK Store test suite was executed against SQLite/MySQL in memory:
- Total Test Cases: **66**
- Total Assertions: **398**
- Status: **100% Passed (OK)**

```bash
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.3.33
Configuration: F:\ORVIK Point\ORVIK Store\phpunit.xml

................................................................. 65 / 66 ( 98%)
.                                                                 66 / 66 (100%)

Time: 00:08.789, Memory: 54.00 MB
OK (66 tests, 398 assertions)
```
