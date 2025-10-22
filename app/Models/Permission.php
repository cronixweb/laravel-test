<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
    ];

    /**
     * Get the roles for the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get permissions grouped by group.
     */
    public static function getGrouped(): array
    {
        return static::all()->groupBy('group')->toArray();
    }
}
