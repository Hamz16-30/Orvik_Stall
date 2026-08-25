<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // System Permissions across Modules
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],

            // Sales & POS
            ['name' => 'sales.view', 'display_name' => 'View Sales', 'module' => 'sales'],
            ['name' => 'sales.create', 'display_name' => 'Create Sales & POS', 'module' => 'sales'],
            ['name' => 'sales.delete', 'display_name' => 'Void / Delete Sales', 'module' => 'sales'],

            // Inventory & Products
            ['name' => 'inventory.view', 'display_name' => 'View Products & Stock', 'module' => 'inventory'],
            ['name' => 'inventory.create', 'display_name' => 'Create Products', 'module' => 'inventory'],
            ['name' => 'inventory.update', 'display_name' => 'Update Products & Stock', 'module' => 'inventory'],
            ['name' => 'inventory.delete', 'display_name' => 'Archive Products', 'module' => 'inventory'],

            // Customers (Phase 5)
            ['name' => 'customers.view', 'display_name' => 'View Customers', 'module' => 'customers'],
            ['name' => 'customers.manage', 'display_name' => 'Manage Customers', 'module' => 'customers'],

            // Suppliers (Phase 5)
            ['name' => 'suppliers.view', 'display_name' => 'View Suppliers', 'module' => 'suppliers'],
            ['name' => 'suppliers.manage', 'display_name' => 'Manage Suppliers', 'module' => 'suppliers'],

            // Purchases (Phase 5)
            ['name' => 'purchases.view', 'display_name' => 'View Purchases', 'module' => 'purchases'],
            ['name' => 'purchases.create', 'display_name' => 'Create Purchases', 'module' => 'purchases'],
            ['name' => 'purchases.void', 'display_name' => 'Void Purchases', 'module' => 'purchases'],

            // Expenses (Phase 5)
            ['name' => 'expenses.view', 'display_name' => 'View Expenses', 'module' => 'expenses'],
            ['name' => 'expenses.create', 'display_name' => 'Create Expenses', 'module' => 'expenses'],
            ['name' => 'expenses.void', 'display_name' => 'Void Expenses', 'module' => 'expenses'],

            // Reports & Exports (Phase 7)
            ['name' => 'reports.view', 'display_name' => 'View Report Center', 'module' => 'reports'],
            ['name' => 'reports.sales', 'display_name' => 'View Sales Reports', 'module' => 'reports'],
            ['name' => 'reports.inventory', 'display_name' => 'View Inventory Reports', 'module' => 'reports'],
            ['name' => 'reports.products', 'display_name' => 'View Product Reports', 'module' => 'reports'],
            ['name' => 'reports.purchases', 'display_name' => 'View Purchase Reports', 'module' => 'reports'],
            ['name' => 'reports.expenses', 'display_name' => 'View Expense Reports', 'module' => 'reports'],
            ['name' => 'reports.customers', 'display_name' => 'View Customer Reports', 'module' => 'reports'],
            ['name' => 'reports.suppliers', 'display_name' => 'View Supplier Reports', 'module' => 'reports'],
            ['name' => 'reports.payments', 'display_name' => 'View Payment Reports', 'module' => 'reports'],
            ['name' => 'reports.profit', 'display_name' => 'View Profit & Margin Reports', 'module' => 'reports'],

            // Settings & Staff
            ['name' => 'settings.view', 'display_name' => 'View Store Settings', 'module' => 'settings'],
            ['name' => 'settings.update', 'display_name' => 'Update Store Settings', 'module' => 'settings'],
            ['name' => 'staff.view', 'display_name' => 'View Staff Members', 'module' => 'staff'],
            ['name' => 'staff.manage', 'display_name' => 'Manage Staff Members', 'module' => 'staff'],
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'module' => 'roles'],
            ['name' => 'roles.manage', 'display_name' => 'Manage Roles', 'module' => 'roles'],
            ['name' => 'audit.view', 'display_name' => 'View Audit Logs', 'module' => 'audit'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Roles Definition
        $roles = [
            Role::OWNER => [
                'display_name' => 'Business Owner',
                'description' => 'Full administrative access and total operational control over the stall.',
                'is_system' => true,
                'permissions' => Permission::all()->pluck('name')->toArray(),
            ],
            Role::MANAGER => [
                'display_name' => 'Store Manager',
                'description' => 'Supervises sales, inventory, purchases, expenses, customers, reports, and operations.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                    'sales.create',
                    'sales.delete',
                    'inventory.view',
                    'inventory.create',
                    'inventory.update',
                    'customers.view',
                    'customers.manage',
                    'suppliers.view',
                    'suppliers.manage',
                    'purchases.view',
                    'purchases.create',
                    'purchases.void',
                    'expenses.view',
                    'expenses.create',
                    'expenses.void',
                    'reports.view',
                    'reports.sales',
                    'reports.inventory',
                    'reports.products',
                    'reports.purchases',
                    'reports.expenses',
                    'reports.customers',
                    'reports.suppliers',
                    'reports.payments',
                    'reports.profit',
                    'staff.view',
                    'audit.view',
                ],
            ],
            Role::CASHIER => [
                'display_name' => 'Cashier',
                'description' => 'Operates the POS register, creates sales, manages customer tags, and views operational sales reports.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                    'sales.create',
                    'inventory.view',
                    'customers.view',
                    'customers.manage',
                    'reports.view',
                    'reports.sales',
                    'reports.products',
                    'reports.inventory',
                    'reports.customers',
                    'reports.payments',
                ],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                ]
            );

            $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
