<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSuspension extends Model
{
    protected $fillable = [
        'business_id',
        'reason',
        'suspended_by',
        'suspended_at',
        'unsuspended_by',
        'unsuspended_at',
    ];

    protected function casts(): array
    {
        return [
            'suspended_at' => 'datetime',
            'unsuspended_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function unsuspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unsuspended_by');
    }
}
