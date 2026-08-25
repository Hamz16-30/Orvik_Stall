<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function pricing(): View
    {
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        return view('landing.pricing', [
            'plans' => $plans,
        ]);
    }

    public function compare(): View
    {
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        $features = Feature::all();

        return view('landing.compare', [
            'plans' => $plans,
            'features' => $features,
        ]);
    }

    public function privacy(): View
    {
        return view('landing.privacy');
    }

    public function terms(): View
    {
        return view('landing.terms');
    }

    public function cookiePolicy(): View
    {
        return view('landing.cookie-policy');
    }
}
