<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log a security or business event.
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $businessId = null,
        ?int $userId = null
    ): AuditLog {
        // Sanitize any sensitive fields if present
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'remember_token', 'secret'];

        if ($oldValues) {
            foreach ($sensitiveKeys as $key) {
                unset($oldValues[$key]);
            }
        }

        if ($newValues) {
            foreach ($sensitiveKeys as $key) {
                unset($newValues[$key]);
            }
        }

        $businessId = $businessId ?? app(TenantContext::class)->getCurrentBusinessId();
        $userId = $userId ?? Auth::id();

        return AuditLog::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
