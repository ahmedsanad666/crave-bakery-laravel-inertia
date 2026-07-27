<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReorderCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Category $category */
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Category $category */
            $category = $this->route('category');
            $parentId = $this->input('parent_id');
            $orderedIds = $this->input('ordered_ids', []);

            if (! in_array($category->id, $orderedIds, true)) {
                $validator->errors()->add(
                    'ordered_ids',
                    'The moved category must be included in the sibling order.'
                );
            }

            if ($parentId && (int) $parentId === $category->id) {
                $validator->errors()->add(
                    'parent_id',
                    'A category cannot be its own parent.'
                );
            }

            $service = app(CategoryService::class);

            if ($parentId && $service->wouldCreateCycle($category, (int) $parentId)) {
                $validator->errors()->add(
                    'parent_id',
                    'Cannot move a category under one of its descendants.'
                );
            }
        });
    }
}
