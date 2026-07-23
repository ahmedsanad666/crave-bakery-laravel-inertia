<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * @return Collection<int, Address>
     */
    public function listForUser(User $user): Collection
    {
        return $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false)
                || $user->addresses()->count() === 0;

            if ($makeDefault) {
                $this->clearDefaults($user);
            }

            return $user->addresses()->create([
                ...$data,
                'is_default' => $makeDefault,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $makeDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : $address->is_default;

            if ($makeDefault) {
                $this->clearDefaults($address->user, $address->id);
            }

            $address->update([
                ...$data,
                'is_default' => $makeDefault,
            ]);

            return $address->fresh();
        });
    }

    public function delete(Address $address): void
    {
        DB::transaction(function () use ($address) {
            $user = $address->user;
            $wasDefault = $address->is_default;

            $address->delete();

            if (! $wasDefault) {
                return;
            }

            $next = $user->addresses()
                ->latest()
                ->first();

            if ($next) {
                $next->update(['is_default' => true]);
            }
        });
    }

    public function setDefault(Address $address): Address
    {
        return DB::transaction(function () use ($address) {
            $this->clearDefaults($address->user, $address->id);

            $address->update(['is_default' => true]);

            return $address->fresh();
        });
    }

    private function clearDefaults(User $user, ?int $exceptId = null): void
    {
        $query = $user->addresses()->where('is_default', true);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_default' => false]);
    }
}
