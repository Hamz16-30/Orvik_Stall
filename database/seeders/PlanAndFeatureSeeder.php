<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlanAndFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Core SaaS Features
        $features = [
            [
                'name' => 'Products Limit',
                'slug' => 'products',
                'description' => 'Maximum number of inventory products that can be created.',
                'type' => Feature::TYPE_LIMIT,
            ],
            [
                'name' => 'Users / Staff Limit',
                'slug' => 'users',
                'description' => 'Maximum staff user accounts allowed for the business.',
                'type' => Feature::TYPE_LIMIT,
            ],
            [
                'name' => 'Monthly Sales Quota',
                'slug' => 'monthly_sales',
                'description' => 'Monthly completed sales transaction allowance.',
                'type' => Feature::TYPE_QUOTA,
            ],
            [
                'name' => 'Customers Limit',
                'slug' => 'customers',
                'description' => 'Maximum customer records allowed.',
                'type' => Feature::TYPE_LIMIT,
            ],
            [
                'name' => 'Suppliers Limit',
                'slug' => 'suppliers',
                'description' => 'Maximum vendor & supplier records allowed.',
                'type' => Feature::TYPE_LIMIT,
            ],
            [
                'name' => 'Excel (.xls) Exports',
                'slug' => 'excel_export',
                'description' => 'Capability to export financial, inventory, and sales reports to Excel spreadsheets.',
                'type' => Feature::TYPE_BOOLEAN,
            ],
            [
                'name' => 'PDF / Clean Print Reports',
                'slug' => 'pdf_export',
                'description' => 'Clean print-ready report generation with stall branding.',
                'type' => Feature::TYPE_BOOLEAN,
            ],
            [
                'name' => 'Advanced Profitability & P&L Reports',
                'slug' => 'advanced_reports',
                'description' => 'Executive profit waterfall, dead stock audit, and gross margin analysis.',
                'type' => Feature::TYPE_BOOLEAN,
            ],
            [
                'name' => 'Advanced Analytics & Insights',
                'slug' => 'advanced_analytics',
                'description' => 'Cohort trends, customer LTV breakdown, and average basket size metrics.',
                'type' => Feature::TYPE_BOOLEAN,
            ],
        ];

        $createdFeatures = [];
        foreach ($features as $f) {
            $createdFeatures[$f['slug']] = Feature::updateOrCreate(
                ['slug' => $f['slug']],
                $f
            );
        }

        // 2. Seed Plans
        $plans = [
            [
                'name' => 'Free',
                'slug' => Plan::SLUG_FREE,
                'description' => 'Essential stall point-of-sale and basic stock tracking for solo vendors.',
                'price' => 0.00,
                'yearly_price' => 0.00,
                'billing_interval' => 'monthly',
                'currency' => 'PKR',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
                'limits' => [
                    'products' => 50,
                    'users' => 1,
                    'monthly_sales' => 100,
                    'customers' => 100,
                    'suppliers' => 20,
                    'excel_export' => false,
                    'pdf_export' => true,
                    'advanced_reports' => false,
                    'advanced_analytics' => false,
                ],
            ],
            [
                'name' => 'Starter',
                'slug' => Plan::SLUG_STARTER,
                'description' => 'Standard stall operations with barcode scanning, invoices, and supplier management.',
                'price' => 1999.00,
                'yearly_price' => 19990.00,
                'billing_interval' => 'monthly',
                'currency' => 'PKR',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 2,
                'limits' => [
                    'products' => 500,
                    'users' => 3,
                    'monthly_sales' => 1000,
                    'customers' => 1000,
                    'suppliers' => 100,
                    'excel_export' => true,
                    'pdf_export' => true,
                    'advanced_reports' => false,
                    'advanced_analytics' => false,
                ],
            ],
            [
                'name' => 'Growth',
                'slug' => Plan::SLUG_GROWTH,
                'description' => 'For expanding retail stalls requiring multi-user access, P&L reporting, and full exports.',
                'price' => 4999.00,
                'yearly_price' => 49990.00,
                'billing_interval' => 'monthly',
                'currency' => 'PKR',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 3,
                'limits' => [
                    'products' => 2500,
                    'users' => 10,
                    'monthly_sales' => 5000,
                    'customers' => 5000,
                    'suppliers' => 500,
                    'excel_export' => true,
                    'pdf_export' => true,
                    'advanced_reports' => true,
                    'advanced_analytics' => true,
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => Plan::SLUG_PRO,
                'description' => 'Unlimited capacity, priority support, and enterprise features for high-volume retail.',
                'price' => 9999.00,
                'yearly_price' => 99990.00,
                'billing_interval' => 'monthly',
                'currency' => 'PKR',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 4,
                'limits' => [
                    'products' => null, // Unlimited
                    'users' => null,    // Unlimited
                    'monthly_sales' => null, // Unlimited
                    'customers' => null,
                    'suppliers' => null,
                    'excel_export' => true,
                    'pdf_export' => true,
                    'advanced_reports' => true,
                    'advanced_analytics' => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $limits = $planData['limits'];
            unset($planData['limits']);

            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            foreach ($limits as $featureSlug => $limitVal) {
                $feature = $createdFeatures[$featureSlug] ?? null;
                if (!$feature) {
                    continue;
                }

                $enabled = $limitVal !== false;
                $limitNumber = is_numeric($limitVal) ? (float) $limitVal : null;

                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'enabled' => $enabled,
                        'limit_value' => $limitNumber,
                    ]
                );
            }
        }

        // 3. Seed Default Platform Settings
        PlatformSetting::set('default_trial_days', 14, 'integer', 'Standard trial duration for newly registered businesses.');
        PlatformSetting::set('default_currency', 'PKR', 'string', 'Base operating and billing currency.');
        PlatformSetting::set('default_plan', 'starter', 'string', 'Default plan assigned during trial period.');
        PlatformSetting::set('maintenance_mode', false, 'boolean', 'Application maintenance mode switch.');
        PlatformSetting::set('registration_enabled', true, 'boolean', 'Public business registration toggle.');
        PlatformSetting::set('ads_enabled', false, 'boolean', 'Monetization ad display on free plans.');
    }
}
