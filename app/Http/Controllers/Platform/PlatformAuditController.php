<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformAuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with(['user', 'business']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('business', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('platform.audit.index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action']),
            'user' => Auth::user(),
        ]);
    }
}
