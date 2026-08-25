<?php

return [
    'name' => env('APP_NAME', 'ORVIK Store'),
    'logo_mark' => '✦',
    'logo_text' => 'ORVIK Store',
    'currency' => env('ORVIK_CURRENCY', 'USD'),
    'currency_symbol' => env('ORVIK_CURRENCY_SYMBOL', '$'),

    'marketing_nav' => [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Features', 'route' => 'features', 'url' => '#features'],
        ['label' => 'Pricing', 'route' => 'pricing', 'url' => '#pricing'],
        ['label' => 'About', 'route' => 'about', 'url' => '#about'],
        ['label' => 'Contact', 'route' => 'contact', 'url' => '#contact'],
    ],

    'app_nav' => [
        ['label' => 'Dashboard', 'route' => 'app.dashboard', 'icon' => 'layout-dashboard', 'badge' => null],
        ['label' => 'Sales', 'route' => 'app.sales', 'icon' => 'shopping-cart', 'badge' => 'POS'],
        ['label' => 'Inventory', 'route' => 'app.inventory', 'icon' => 'boxes', 'badge' => 'Low: 3'],
        ['label' => 'Reports', 'route' => 'app.reports', 'icon' => 'bar-chart-3', 'badge' => null],
        ['label' => 'Customers', 'route' => 'app.customers', 'icon' => 'users', 'badge' => null],
        ['label' => 'Settings', 'route' => 'app.settings', 'icon' => 'settings', 'badge' => null],
    ],

    'contact' => [
        'email' => 'support@orvikstore.com',
        'phone' => '+1 (555) 019-2834',
    ],
];
