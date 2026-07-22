<?php

namespace App\Http\Resources;

use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => \App\Services\AdminProfileService::avatarUrl($this->avatar),
            'role' => $this->role,
            'status' => $this->status ?? 'active',
            'permissions' => $this->role === 'super_admin'
                ? null
                : AdminPermissions::normalize($this->permissions),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
