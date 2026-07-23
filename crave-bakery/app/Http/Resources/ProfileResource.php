<?php

namespace App\Http\Resources;

use App\Services\AdminProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        [$firstName, $lastName] = $this->splitName((string) ($this->name ?? ''));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'avatar' => AdminProfileService::avatarUrl($this->avatar),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }
}
