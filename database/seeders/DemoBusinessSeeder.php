<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();
        $managerRole = Role::where('name', Role::MANAGER)->first();
        $cashierRole = Role::where('name', Role::CASHIER)->first();

        // 0. Platform Super Admin (Layer 2)
        $admin = User::updateOrCreate(
            ['email' => 'admin@orvikstore.com'],
            [
                'name' => 'Platform Administrator',
                'password' => Hash::make('password'),
                'is_platform_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 1. Business A: ORVIK Main Stall #04
        $ownerA = User::updateOrCreate(
            ['email' => 'owner@orvikstore.com'],
            [
                'name' => 'Tariq Mehmood',
                'password' => Hash::make('password'),
                'is_platform_admin' => true, // Owner can also test platform admin
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $businessA = Business::updateOrCreate(
            ['slug' => 'orvik-main-stall-04'],
            [
                'name' => 'ORVIK Main Stall #04',
                'owner_id' => $ownerA->id,
                'email' => 'stall04@orvikstore.com',
                'phone' => '+92 300 1234567',
                'address' => 'Counter 4, Main Commercial Bazaar',
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
            ]
        );

        BusinessUser::updateOrCreate(
            ['business_id' => $businessA->id, 'user_id' => $ownerA->id],
            ['role_id' => $ownerRole->id, 'status' => 'active', 'joined_at' => now()]
        );

        // Manager in Business A
        $managerA = User::updateOrCreate(
            ['email' => 'manager@orvikstore.com'],
            [
                'name' => 'Sara Khan',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        BusinessUser::updateOrCreate(
            ['business_id' => $businessA->id, 'user_id' => $managerA->id],
            ['role_id' => $managerRole->id, 'status' => 'active', 'joined_at' => now()]
        );

        // Cashier in Business A
        $cashierA = User::updateOrCreate(
            ['email' => 'cashier@orvikstore.com'],
            [
                'name' => 'Hamza Ali',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        BusinessUser::updateOrCreate(
            ['business_id' => $businessA->id, 'user_id' => $cashierA->id],
            ['role_id' => $cashierRole->id, 'status' => 'active', 'joined_at' => now()]
        );

        // 2. Business B: Artisan Beta (For Tenant Isolation Testing)
        $ownerB = User::updateOrCreate(
            ['email' => 'beta_owner@orvikstore.com'],
            [
                'name' => 'Bilal Ahmed',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $businessB = Business::updateOrCreate(
            ['slug' => 'artisan-beta-stall'],
            [
                'name' => 'Artisan Beta Stall',
                'owner_id' => $ownerB->id,
                'email' => 'beta@orvikstore.com',
                'currency' => 'USD',
                'timezone' => 'UTC',
                'status' => 'active',
            ]
        );

        BusinessUser::updateOrCreate(
            ['business_id' => $businessB->id, 'user_id' => $ownerB->id],
            ['role_id' => $ownerRole->id, 'status' => 'active', 'joined_at' => now()]
        );

        // 3. Demo Categories for Business A
        $catDrinks = Category::updateOrCreate(
            ['business_id' => $businessA->id, 'name' => 'Cold Drinks'],
            ['description' => 'Chilled beverages, waters, and sodas', 'status' => 'active']
        );

        $catSnacks = Category::updateOrCreate(
            ['business_id' => $businessA->id, 'name' => 'Snacks & Chips'],
            ['description' => 'Crisps, nimco, and savory snacks', 'status' => 'active']
        );

        $catBakery = Category::updateOrCreate(
            ['business_id' => $businessA->id, 'name' => 'Biscuits & Bakery'],
            ['description' => 'Sweet biscuits, cookies, and cakes', 'status' => 'active']
        );

        $catTea = Category::updateOrCreate(
            ['business_id' => $businessA->id, 'name' => 'Hot Beverages'],
            ['description' => 'Tea, coffee, and green tea', 'status' => 'active']
        );

        // 4. Demo Products for Business A
        $demoProducts = [
            [
                'name' => 'Mineral Water 500ml',
                'category_id' => $catDrinks->id,
                'sku' => 'WTR-500',
                'barcode' => '8964000123456',
                'cost_price' => 40.00,
                'selling_price' => 60.00,
                'stock_quantity' => 48.000,
                'low_stock_threshold' => 10.000,
                'unit' => 'bottle',
            ],
            [
                'name' => 'Coca Cola 330ml Can',
                'category_id' => $catDrinks->id,
                'sku' => 'CC-330',
                'barcode' => '8964000654321',
                'cost_price' => 70.00,
                'selling_price' => 100.00,
                'stock_quantity' => 24.000,
                'low_stock_threshold' => 8.000,
                'unit' => 'can',
            ],
            [
                'name' => 'Lays Masala 65g',
                'category_id' => $catSnacks->id,
                'sku' => 'LAYS-MAS-65',
                'barcode' => '8964000789123',
                'cost_price' => 45.00,
                'selling_price' => 60.00,
                'stock_quantity' => 4.000, // Low stock
                'low_stock_threshold' => 10.000,
                'unit' => 'pack',
            ],
            [
                'name' => 'Oreo Original 133g',
                'category_id' => $catBakery->id,
                'sku' => 'OREO-133',
                'barcode' => '8964000345678',
                'cost_price' => 85.00,
                'selling_price' => 120.00,
                'stock_quantity' => 0.000, // Out of stock
                'low_stock_threshold' => 5.000,
                'unit' => 'pack',
            ],
            [
                'name' => 'Karak Chai Special',
                'category_id' => $catTea->id,
                'sku' => 'TEA-KRK',
                'cost_price' => 30.00,
                'selling_price' => 60.00,
                'stock_quantity' => 100.000,
                'low_stock_threshold' => 15.000,
                'unit' => 'cup',
            ],
            [
                'name' => 'Gourmet Salted Chips',
                'category_id' => $catSnacks->id,
                'sku' => 'GMT-SLT-50',
                'cost_price' => 50.00,
                'selling_price' => 75.00,
                'stock_quantity' => 35.000,
                'low_stock_threshold' => 10.000,
                'unit' => 'pack',
            ],
        ];

        foreach ($demoProducts as $pData) {
            $product = Product::updateOrCreate(
                ['business_id' => $businessA->id, 'name' => $pData['name']],
                array_merge($pData, ['status' => 'active', 'track_inventory' => true])
            );

            if ($product->stock_quantity > 0) {
                StockMovement::firstOrCreate(
                    ['business_id' => $businessA->id, 'product_id' => $product->id, 'type' => StockMovement::TYPE_OPENING],
                    [
                        'user_id' => $ownerA->id,
                        'quantity' => $product->stock_quantity,
                        'previous_quantity' => 0,
                        'new_quantity' => $product->stock_quantity,
                        'reason' => 'Initial stock on catalog setup',
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 5. Attach Subscriptions to Demo Businesses
        $growthPlan = \App\Models\Plan::where('slug', \App\Models\Plan::SLUG_GROWTH)->first();
        if ($growthPlan) {
            \App\Models\Subscription::updateOrCreate(
                ['business_id' => $businessA->id],
                [
                    'plan_id' => $growthPlan->id,
                    'status' => \App\Models\Subscription::STATUS_ACTIVE,
                    'starts_at' => now()->subDays(5),
                    'ends_at' => now()->addDays(25),
                    'provider' => 'manual',
                ]
            );
        }

        $starterPlan = \App\Models\Plan::where('slug', \App\Models\Plan::SLUG_STARTER)->first();
        if ($starterPlan) {
            \App\Models\Subscription::updateOrCreate(
                ['business_id' => $businessB->id],
                [
                    'plan_id' => $starterPlan->id,
                    'status' => \App\Models\Subscription::STATUS_TRIALING,
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(14),
                    'provider' => 'manual',
                ]
            );
        }
    }
}
