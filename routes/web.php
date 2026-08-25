<?php

use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Platform\PlatformAnalyticsController;
use App\Http\Controllers\Platform\PlatformAuditController;
use App\Http\Controllers\Platform\PlatformBusinessController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformFeatureController;
use App\Http\Controllers\Platform\PlatformPlanController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Settings\AccountSettingsController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\BusinessSettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — ORVIK Store
|--------------------------------------------------------------------------
*/

// ==========================================
// Public Marketing, Pricing & Legal Pages
// ==========================================
Route::get('/', function () {
    return view('landing.index');
})->name('home');

Route::get('/pricing', [PricingController::class, 'pricing'])->name('pricing');
Route::get('/pricing/compare', [PricingController::class, 'compare'])->name('pricing.compare');
Route::get('/privacy', [PricingController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PricingController::class, 'terms'])->name('terms');
Route::get('/cookie-policy', [PricingController::class, 'cookiePolicy'])->name('cookie.policy');

// Payment Webhooks
Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])->name('webhooks');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'createForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'createReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'storeReset'])->name('password.update');

    // Public Invitation Acceptance
    Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/accept/{token}', [InvitationController::class, 'processAccept'])->name('invitations.process');
});

// Authenticated Logout (Available globally & under app)
Route::post('/app/logout', [LogoutController::class, 'destroy'])->name('app.logout')->middleware('auth');
Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout')->middleware('auth');

// ==========================================
// ORVIK Platform Administration (Layer 2)
// ==========================================
Route::middleware(['auth', 'platform.admin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [PlatformDashboardController::class, 'index']);

    // Businesses
    Route::get('/businesses', [PlatformBusinessController::class, 'index'])->name('businesses');
    Route::get('/businesses/{business}', [PlatformBusinessController::class, 'show'])->name('businesses.show');
    Route::post('/businesses/{business}/suspend', [PlatformBusinessController::class, 'suspend'])->name('businesses.suspend');
    Route::post('/businesses/{business}/unsuspend', [PlatformBusinessController::class, 'unsuspend'])->name('businesses.unsuspend');
    Route::post('/businesses/{business}/plan', [PlatformBusinessController::class, 'changePlan'])->name('businesses.plan');

    // Plans CRUD
    Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [PlatformPlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [PlatformPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [PlatformPlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlatformPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlatformPlanController::class, 'destroy'])->name('plans.destroy');
    Route::get('/plans/{plan}/features', [PlatformPlanController::class, 'features'])->name('plans.features');
    Route::post('/plans/{plan}/features', [PlatformPlanController::class, 'updateFeatures'])->name('plans.features.update');

    // Features CRUD
    Route::get('/features', [PlatformFeatureController::class, 'index'])->name('features.index');
    Route::post('/features', [PlatformFeatureController::class, 'store'])->name('features.store');
    Route::put('/features/{feature}', [PlatformFeatureController::class, 'update'])->name('features.update');

    // SaaS Analytics
    Route::get('/analytics', [PlatformAnalyticsController::class, 'index'])->name('analytics');

    // System Settings & Maintenance Mode
    Route::get('/settings', [PlatformSettingController::class, 'index'])->name('settings');
    Route::post('/settings', [PlatformSettingController::class, 'update'])->name('settings.update');

    // SaaS Audit Trail
    Route::get('/audit', [PlatformAuditController::class, 'index'])->name('audit');
});

// ==========================================
// Authenticated Multi-Tenant Business App (Layer 1)
// ==========================================
Route::middleware(['auth', 'business', 'active.account'])->prefix('app')->name('app.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS Sales Engine
    Route::get('/sales', [POSController::class, 'index'])->name('sales')->middleware('permission:sales.create');
    Route::post('/sales/complete', [POSController::class, 'store'])->name('sales.complete')->middleware('permission:sales.create');

    // Sales Management & Invoices
    Route::get('/sales/history', [SaleController::class, 'index'])->name('sales.history')->middleware('permission:sales.view');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show')->middleware('permission:sales.view');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt')->middleware('permission:sales.view');
    Route::post('/sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void')->middleware('permission:sales.void');

    // Inventory & Products
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('permission:inventory.view');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create')->middleware('permission:inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store')->middleware('permission:inventory.create');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show')->middleware('permission:inventory.view');
    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit')->middleware('permission:inventory.update');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('permission:inventory.update');
    Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy')->middleware('permission:inventory.delete');
    Route::post('/inventory/{id}/restore', [InventoryController::class, 'restore'])->name('inventory.restore')->middleware('permission:inventory.create');

    // Stock Adjustments
    Route::post('/inventory/{product}/adjust-stock', [StockAdjustmentController::class, 'store'])->name('inventory.adjust')->middleware('permission:inventory.update');
    Route::post('/inventory/{product}/stock-adjustment', [StockAdjustmentController::class, 'store'])->middleware('permission:inventory.update');

    // Categories
    Route::get('/inventory/categories', [CategoryController::class, 'index'])->name('inventory.categories.index')->middleware('permission:inventory.view');
    Route::get('/inventory/categories/create', [CategoryController::class, 'create'])->name('inventory.categories.create')->middleware('permission:inventory.create');
    Route::post('/inventory/categories', [CategoryController::class, 'store'])->name('inventory.categories.store')->middleware('permission:inventory.create');
    Route::get('/inventory/categories/{category}/edit', [CategoryController::class, 'edit'])->name('inventory.categories.edit')->middleware('permission:inventory.update');
    Route::put('/inventory/categories/{category}', [CategoryController::class, 'update'])->name('inventory.categories.update')->middleware('permission:inventory.update');
    Route::delete('/inventory/categories/{category}', [CategoryController::class, 'destroy'])->name('inventory.categories.destroy')->middleware('permission:inventory.delete');

    // Customers Module
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:customers.view');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create')->middleware('permission:customers.manage');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:customers.manage');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show')->middleware('permission:customers.view');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:customers.manage');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:customers.manage');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:customers.manage');

    // Suppliers Module
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('permission:suppliers.view');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create')->middleware('permission:suppliers.manage');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('permission:suppliers.manage');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show')->middleware('permission:suppliers.view');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('permission:suppliers.manage');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('permission:suppliers.manage');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('permission:suppliers.manage');

    // Purchases Module
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('permission:purchases.view');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:purchases.create');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:purchases.view');
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt')->middleware('permission:purchases.view');
    Route::post('/purchases/{purchase}/void', [PurchaseController::class, 'void'])->name('purchases.void')->middleware('permission:purchases.void');

    // Expenses Module
    Route::get('/expenses/categories', [ExpenseCategoryController::class, 'index'])->name('expenses.categories.index')->middleware('permission:expenses.view');
    Route::post('/expenses/categories', [ExpenseCategoryController::class, 'store'])->name('expenses.categories.store')->middleware('permission:expenses.create');
    Route::put('/expenses/categories/{category}', [ExpenseCategoryController::class, 'update'])->name('expenses.categories.update')->middleware('permission:expenses.create');
    Route::delete('/expenses/categories/{category}', [ExpenseCategoryController::class, 'destroy'])->name('expenses.categories.destroy')->middleware('permission:expenses.create');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:expenses.create');
    Route::post('/expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void')->middleware('permission:expenses.void');

    // Reporting & Export Engine
    Route::get('/reports', [ReportController::class, 'index'])->name('reports')->middleware('permission:reports.view');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales')->middleware('permission:reports.sales');
    Route::get('/reports/sales/daily', [ReportController::class, 'dailySales'])->name('reports.sales.daily')->middleware('permission:reports.sales');
    Route::get('/reports/sales/weekly', [ReportController::class, 'weeklySales'])->name('reports.sales.weekly')->middleware('permission:reports.sales');
    Route::get('/reports/sales/monthly', [ReportController::class, 'monthlySales'])->name('reports.sales.monthly')->middleware('permission:reports.sales');

    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products')->middleware('permission:reports.products');
    Route::get('/reports/products/categories', [ReportController::class, 'productCategories'])->name('reports.products.categories')->middleware('permission:reports.products');

    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory')->middleware('permission:reports.inventory');
    Route::get('/reports/inventory/movements', [ReportController::class, 'stockMovements'])->name('reports.inventory.movements')->middleware('permission:reports.inventory');

    Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases')->middleware('permission:reports.purchases');
    Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers')->middleware('permission:reports.suppliers');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers')->middleware('permission:reports.customers');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses')->middleware('permission:reports.expenses');
    Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments')->middleware('permission:reports.payments');
    Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit')->middleware('permission:reports.profit');

    // Export & Print Endpoints
    Route::get('/reports/{type}/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/{type}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/{type}/print', [ReportController::class, 'printReport'])->name('reports.print');

    // Settings Navigation Hub
    Route::get('/settings', function () {
        return view('app.settings.index');
    })->name('settings');

    // SaaS Plan & Resource Usage Dashboard
    Route::get('/settings/plan', [BillingController::class, 'plan'])->name('settings.plan');
    Route::get('/settings/billing', [BillingController::class, 'billing'])->name('settings.billing');
    Route::post('/settings/billing/upgrade', [BillingController::class, 'upgrade'])->name('settings.billing.upgrade');
    Route::post('/settings/billing/cancel', [BillingController::class, 'cancel'])->name('settings.billing.cancel');
    Route::post('/settings/billing/reactivate', [BillingController::class, 'reactivate'])->name('settings.billing.reactivate');

    // Account Settings
    Route::get('/settings/account', [AccountSettingsController::class, 'index'])->name('settings.account');
    Route::put('/settings/account/profile', [AccountSettingsController::class, 'updateProfile'])->name('settings.account.profile');
    Route::put('/settings/account/password', [AccountSettingsController::class, 'changePassword'])->name('settings.account.password');

    // Business Settings (Owner / Authorized)
    Route::get('/settings/business', [BusinessSettingsController::class, 'index'])->name('settings.business')->middleware('permission:settings.view');
    Route::put('/settings/business', [BusinessSettingsController::class, 'update'])->name('settings.business.update')->middleware('permission:settings.update');

    // Staff Management (Owner / Authorized)
    Route::get('/settings/staff', [StaffController::class, 'index'])->name('settings.staff')->middleware('permission:staff.view');
    Route::post('/settings/staff/invite', [InvitationController::class, 'store'])->name('settings.staff.invite')->middleware('permission:staff.manage');
    Route::delete('/settings/invitations/{invitation}', [InvitationController::class, 'cancel'])->name('settings.invitations.cancel')->middleware('permission:staff.manage');
    Route::put('/settings/staff/{user}/role', [StaffController::class, 'updateRole'])->name('settings.staff.role')->middleware('permission:staff.manage');
    Route::put('/settings/staff/{user}/suspend', [StaffController::class, 'suspend'])->name('settings.staff.suspend')->middleware('permission:staff.manage');
    Route::delete('/settings/staff/{user}/deactivate', [StaffController::class, 'deactivate'])->name('settings.staff.deactivate')->middleware('permission:staff.manage');

    // Roles & Permissions Management
    Route::get('/settings/roles', [RoleController::class, 'index'])->name('settings.roles')->middleware('permission:roles.view');
    Route::post('/settings/roles', [RoleController::class, 'store'])->name('settings.roles.store')->middleware('permission:roles.manage');
    Route::put('/settings/roles/{role}', [RoleController::class, 'update'])->name('settings.roles.update')->middleware('permission:roles.manage');
    Route::delete('/settings/roles/{role}', [RoleController::class, 'destroy'])->name('settings.roles.destroy')->middleware('permission:roles.manage');

    // Audit Logs
    Route::get('/settings/audit', [AuditLogController::class, 'index'])->name('settings.audit')->middleware('permission:audit.view');
});
