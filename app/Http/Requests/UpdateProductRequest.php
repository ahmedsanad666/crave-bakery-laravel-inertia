<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesProduct;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    use ValidatesProduct;

    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()->can('update', $product);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('allow_backorders')) {
            $this->merge(['allow_backorders' => $this->boolean('allow_backorders')]);
        }

        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }

        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return array_merge($this->productRules($product->id), [
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'og_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Product $product */
            $product = $this->route('product');

            $removeIds = collect($this->input('remove_image_ids', []))
                ->map(fn ($id) => (int) $id)
                ->all();

            $existingRemaining = $product->images()
                ->when($removeIds !== [], fn ($query) => $query->whereNotIn('id', $removeIds))
                ->count();

            $newImages = $this->file('images', []);
            $newCount = is_array($newImages) ? count($newImages) : 0;

            if (($existingRemaining + $newCount) < 1) {
                $validator->errors()->add(
                    'images',
                    'At least one gallery image is required.'
                );
            }
        });
    }
}
