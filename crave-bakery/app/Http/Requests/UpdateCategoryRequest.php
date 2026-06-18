<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesCategory;
use App\Models\Category;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        return $this->categoryRules($category->id);
    }
    /**
     * قواعد إضافية: التصنيف لا يمكن أن يكون أباً لنفسه
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');
            if ($parentId && (int) $parentId === $category->id) {
                $validator->errors()->add(
                    'parent_id',
                    'A category cannot be its own parent.'
                );
            }
        });
    }
}
