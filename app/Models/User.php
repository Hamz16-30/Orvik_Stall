<?php

namespace App\Models;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'is_platform_admin',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user')
            ->withPivot('id', 'role_id', 'status', 'joined_at')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function currentBusiness(): ?Business
    {
        return app(TenantContext::class)->getCurrentBusiness();
    }

    public function currentMembership(): ?BusinessUser
    {
        $business = $this->currentBusiness();
        if (!$business) {
            return null;
        }

        return BusinessUser::where('business_id', $business->id)
            ->where('user_id', $this->id)
            ->with('role')
            ->first();
    }

    public function currentRole(): ?Role
    {
        return $this->currentMembership()?->role;
    }

    public function hasPermissionTo(string $permission): bool
    {
        $role = $this->currentRole();
        if (!$role) {
            return false;
        }

        return $role->hasPermission($permission);
    }

    public function isOwner(): bool
    {
        $role = $this->currentRole();
        return $role && $role->name === Role::OWNER;
    }

    public function isManager(): bool
    {
        $role = $this->currentRole();
        return $role && $role->name === Role::MANAGER;
    }

    public function isCashier(): bool
    {
        $role = $this->currentRole();
        return $role && $role->name === Role::CASHIER;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
