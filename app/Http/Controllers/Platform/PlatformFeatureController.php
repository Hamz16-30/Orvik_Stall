<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformFeatureController extends Controller
{
    public function index(): View
    {
        $features = Feature::withCount('plans')->get();

        return view('platform.features.index', [
            'features' => $features,
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:features,slug',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:BOOLEAN,LIMIT,QUOTA',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $feature = Feature::create($validated);

        // Attach to all existing plans
        $plans = Plan::all();
        foreach ($plans as $plan) {
            PlanFeature::create([
                'plan_id' => $plan->id,
                'feature_id' => $feature->id,
                'enabled' => false,
                'limit_value' => $feature->type === Feature::TYPE_BOOLEAN ? null : 0,
            ]);
        }

        AuditLogger::log('platform.feature_created', 'Feature', $feature->id, ['name' => $feature->name], null, Auth::id());

        return back()->with('success', "Feature '{$feature->name}' created and added to plan matrices.");
    }

    public function update(Request $request, Feature $feature): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:BOOLEAN,LIMIT,QUOTA',
        ]);

        $feature->update($validated);
        AuditLogger::log('platform.feature_updated', 'Feature', $feature->id, ['name' => $feature->name], null, Auth::id());

        return back()->with('success', "Feature '{$feature->name}' updated successfully.");
    }
}
