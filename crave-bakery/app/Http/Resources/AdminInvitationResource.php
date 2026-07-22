<?php

namespace App\Http\Resources;

use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'permissions' => $this->role === 'super_admin'
                ? null
                : AdminPermissions::normalize($this->permissions),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'is_pending' => $this->isPending(),
            'invited_by' => $this->when(
                $this->relationLoaded('invitedBy') && $this->invitedBy,
                fn () => [
                    'id' => $this->invitedBy->id,
                    'name' => $this->invitedBy->name,
                    'email' => $this->invitedBy->email,
                ],
            ),
        ];
    }
}
