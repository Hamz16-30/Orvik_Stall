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

class PlatformPlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::with('features')
            ->withCount(['subscriptions' => fn($q) => $q->whereIn('status', ['ACTIVE', 'TRIALING'])])
            ->orderBy('sort_order')
            ->get();

        return view('platform.plans.index', [
            'plans' => $plans,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        return view('platform.plans.create', [
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:plans,slug',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'billing_interval' => 'required|in:monthly,yearly',
            'currency' => 'required|string|max:10',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_public'] = $request->has('is_public');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $plan = Plan::create($validated);

        // Attach all features with default false/0
        $features = Feature::all();
        foreach ($features as $feature) {
            PlanFeature::create([
                'plan_id' => $plan->id,
                'feature_id' => $feature->id,
                'enabled' => false,
                'limit_value' => $feature->type === Feature::TYPE_BOOLEAN ? null : 0,
            ]);
        }

        AuditLogger::log('platform.plan_created', 'Plan', $plan->id, ['name' => $plan->name], null, Auth::id());

        return redirect()->route('platform.plans.features', $plan->id)->with('success', 'Plan created. Now configure its features and limits.');
    }

    public function edit(Plan $plan): View
    {
        return view('platform.plans.edit', [
            'plan' => $plan,
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'billing_interval' => 'required|in:monthly,yearly',
            'currency' => 'required|string|max:10',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_public'] = $request->has('is_public');

        $plan->update($validated);

        AuditLogger::log('platform.plan_updated', 'Plan', $plan->id, ['name' => $plan->name], null, Auth::id());

        return redirect()->route('platform.plans.index')->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    public function features(Plan $plan): View
    {
        $plan->load('features');
        $allFeatures = Feature::all();

        return view('platform.plans.features', [
            'plan' => $plan,
            'features' => $allFeatures,
            'user' => Auth::user(),
        ]);
    }

    public function updateFeatures(Request $request, Plan $plan): RedirectResponse
    {
        $featureInputs = $request->input('features', []);

        foreach ($featureInputs as $featureId => $data) {
            $enabled = isset($data['enabled']) && (bool)$data['enabled'];
            $limitValue = null;

            if (isset($data['limit_value']) && $data['limit_value'] !== '') {
                $limitValue = (float) $data['limit_value'];
            }

            PlanFeature::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'feature_id' => $featureId,
                ],
                [
                    'enabled' => $enabled,
                    'limit_value' => $limitValue,
                ]
            );
        }

        AuditLogger::log('platform.plan_features_updated', 'Plan', $plan->id, ['name' => $plan->name], null, Auth::id());

        return back()->with('success', "Feature matrix for '{$plan->name}' updated successfully.");
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $activeSubCount = $plan->subscriptions()->whereIn('status', ['ACTIVE', 'TRIALING'])->count();
        if ($activeSubCount > 0) {
            return back()->withErrors(['plan' => "Cannot archive plan '{$plan->name}' because it currently has {$activeSubCount} active subscriber(s)."]);
        }

        $plan->delete();
        AuditLogger::log('platform.plan_archived', 'Plan', $plan->id, ['name' => $plan->name], null, Auth::id());

        return redirect()->route('platform.plans.index')->with('success', "Plan '{$plan->name}' has been archived.");
    }
}
