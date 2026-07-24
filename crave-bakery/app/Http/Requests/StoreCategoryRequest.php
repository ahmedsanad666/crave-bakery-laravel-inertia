<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesCategory;
use App\Models\Category;

class StoreCategoryRequest extends FormRequest
{
    use ValidatesCategory;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('parent_id') === '' || $this->input('parent_id') === 'null') {
            $this->merge(['parent_id' => null]);
        }

        $this->merge([
            'show_in_navigation' => $this->boolean('show_in_navigation'),
            'show_in_footer' => $this->boolean('show_in_footer'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->categoryRules(), [
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);
    }
}
