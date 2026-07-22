<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Admin / super_admin subset of users (same table).
 * Keeps CustomerPolicy on User while AdminUserPolicy applies here.
 */
class AdminUser extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('admins', function (Builder $query) {
            $query->whereIn('role', ['admin', 'super_admin']);
        });
    }

    public function getMorphClass(): string
    {
        return User::class;
    }
}
