<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayModel extends Model
{
    protected $table = 'payment_gateways';

    protected $fillable = [
        'name',
        'label',
        'description',
        'logo',
        'is_enabled',
        'is_test_mode',
        'config',
        'instructions',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_test_mode' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Decrypt and decode the config JSON when reading.
     * Encrypt and encode when writing.
     *
     * @return Attribute<array<string, mixed>, array<string, mixed>|null>
     */
    protected function config(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): array {
                if (! $value) {
                    return [];
                }

                try {
                    return json_decode(decrypt($value), true) ?? [];
                } catch (\Exception) {
                    return [];
                }
            },
            set: function (array|null $value): ?string {
                if (! $value) {
                    return null;
                }

                return encrypt(json_encode($value));
            },
        );
    }

    /**
     * Only return gateways the admin has enabled.
     *
     * @param  Builder<PaymentGatewayModel>  $query
     * @return Builder<PaymentGatewayModel>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true)->orderBy('sort_order');
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
