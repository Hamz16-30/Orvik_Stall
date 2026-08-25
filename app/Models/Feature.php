<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    const TYPE_BOOLEAN = 'BOOLEAN';
    const TYPE_LIMIT = 'LIMIT';
    const TYPE_QUOTA = 'QUOTA';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot('enabled', 'limit_value')
            ->withTimestamps();
    }
}
