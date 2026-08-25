<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBusinessRequest;
use App\Models\Business;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $business = $tenantContext->getCurrentBusiness();
        $this->authorize('view', $business);

        return view('app.settings.business.index', [
            'business' => $business,
        ]);
    }

    public function update(UpdateBusinessRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();
        $this->authorize('update', $business);

        $oldValues = $business->only(['name', 'email', 'phone', 'address', 'currency', 'timezone']);

        $business->update($request->validated());

        AuditLogger::log(
            action: 'business.updated',
            entityType: Business::class,
            entityId: $business->id,
            oldValues: $oldValues,
            newValues: $request->validated()
        );

        return back()->with('success', 'Stall settings updated successfully.');
    }
}
