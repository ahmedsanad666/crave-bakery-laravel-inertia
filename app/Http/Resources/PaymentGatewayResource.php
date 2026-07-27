<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PaymentGatewayModel */
class PaymentGatewayResource extends JsonResource
{
    /**
     * Public checkout-safe fields only — never expose config secrets.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'logo' => $this->logo,
            'instructions' => $this->instructions,
            'sort_order' => $this->sort_order,
        ];
    }
}
