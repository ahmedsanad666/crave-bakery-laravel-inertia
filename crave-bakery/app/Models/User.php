<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;
use App\Support\AdminPermissions;


#[Fillable(['name', 'email', 'password', 'role', 'avatar', 'phone', 'date_of_birth', 'gender', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'permissions' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    // helper methods

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasPermission(string $scope, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->isAdmin()) {
            return false;
        }

        if (! AdminPermissions::isValid($scope, $action)) {
            return false;
        }

        return ($this->permissions[$scope][$action] ?? false) === true;
    }

    // relations 

    public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}
public function carts(): HasMany
{
    return $this->hasMany(Cart::class);
}
public function favourites(): HasMany
{
    return $this->hasMany(Favourite::class);
}
public function collections(): HasMany
{
    return $this->hasMany(Collection::class);
}
public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
public function addresses(): HasMany
{
    return $this->hasMany(Address::class);
}
public function sentInvitations(): HasMany
{
    return $this->hasMany(AdminInvitation::class, 'invited_by');
}

public function defaultRedirectRoute():string{
    return ($this->isAdmin() || $this->isSuperAdmin())? 'admin.dashboard' : 'dashboard';
}
// accessors

public function getDefaultAddressAttribute(): ?Address
{
    return $this->addresses()->where('is_default', true)->first();
}
}
