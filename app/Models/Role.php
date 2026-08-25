<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    const OWNER = 'owner';
    const MANAGER = 'manager';
    const CASHIER = 'cashier';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        // Owner role implicitly has all permissions
        if ($this->name === self::OWNER) {
            return true;
        }

        return $this->permissions->contains('name', $permissionName);
    }

    public function isSystemRole(): bool
    {
        return (bool) $this->is_system;
    }
}
