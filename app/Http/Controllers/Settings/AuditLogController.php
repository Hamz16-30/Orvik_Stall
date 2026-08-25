<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\TenantContext;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $this->middleware('permission:audit.view');

        $business = $tenantContext->getCurrentBusiness();

        $logs = AuditLog::with('user')
            ->where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('app.settings.audit.index', [
            'business' => $business,
            'logs' => $logs,
        ]);
    }
}
