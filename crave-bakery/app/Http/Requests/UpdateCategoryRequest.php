<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCategory;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    use ValidatesCategory;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $this->user()->can('update', $category);
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('parent_id') === '' || $this->input('parent_id') === 'null') {
            $this->merge(['parent_id' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return array_merge($this->categoryRules($category->id), [
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Category $category */
            $category = $this->route('category');
            $parentId = $this->input('parent_id');

            if ($parentId === null || $parentId === '') {
                return;
            }

            if (app(CategoryService::class)->wouldCreateCycle($category, (int) $parentId)) {
                $validator->errors()->add(
                    'parent_id',
                    'Cannot move a category under one of its descendants.'
                );
            }
        });
    }
}
