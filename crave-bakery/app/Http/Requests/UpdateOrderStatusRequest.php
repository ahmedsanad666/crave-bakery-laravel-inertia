<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Order $order */
        $order = $this->route('order');

        return $this->user()->can('update', $order);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('notify_customer')) {
            $this->merge([
                'notify_customer' => $this->boolean('notify_customer'),
            ]);
        }
    }
}
