<?php

namespace App\Services;

use App\Models\AdminInvitation;
use App\Models\AdminUser;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    /**
     * @return array{
     *     total: int,
     *     super_admins: int,
     *     active: int,
     *     pending_invites: int
     * }
     */
    public function stats(): array
    {
        return [
            'total' => AdminUser::query()->count(),
            'super_admins' => AdminUser::query()->where('role', 'super_admin')->count(),
            'active' => AdminUser::query()->where('status', 'active')->count(),
            'pending_invites' => AdminInvitation::query()
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $search = $filters['search'] ?? null;

        return AdminUser::query()
            ->when(filled($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(filled($filters['role'] ?? null), fn ($q) => $q->where('role', $filters['role']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate(max(1, min($perPage, 100)))
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $permissions
     */
    public function updatePermissions(AdminUser $adminUser, array $permissions): AdminUser
    {
        if ($adminUser->isSuperAdmin()) {
            $adminUser->permissions = null;
            $adminUser->save();

            return $adminUser->fresh();
        }

        $adminUser->permissions = AdminPermissions::normalize($permissions);
        $adminUser->save();

        return $adminUser->fresh();
    }

    public function deactivate(AdminUser $adminUser, User $actor): AdminUser
    {
        $this->guardDestructiveAction($adminUser, $actor, 'deactivate');

        $adminUser->status = 'inactive';
        $adminUser->save();

        return $adminUser->fresh();
    }

    public function delete(AdminUser $adminUser, User $actor): void
    {
        $this->guardDestructiveAction($adminUser, $actor, 'delete');

        $adminUser->delete();
    }

    private function guardDestructiveAction(AdminUser $adminUser, User $actor, string $action): void
    {
        if ($actor->id === $adminUser->id) {
            throw ValidationException::withMessages([
                'admin' => "You cannot {$action} your own account.",
            ]);
        }

        if ($adminUser->isSuperAdmin() && $this->superAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'admin' => "Cannot {$action} the last super admin.",
            ]);
        }
    }

    private function superAdminCount(): int
    {
        return User::query()
            ->where('role', 'super_admin')
            ->count();
    }
}
