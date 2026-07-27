<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use App\Models\Category;

trait ValidatesCategory
{

    protected function categoryRules(?int $categoryId = null): array
    {

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Category::class)->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists(Category::class, 'id'),
            ],
            'status' => ['required', Rule::in([
                'active',
                'draft',
                'archived'
            ])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'default_sort' => ['nullable', 'string', 'max:50'],
            'show_in_navigation' => ['sometimes', 'boolean'],
            'show_in_footer' => ['sometimes', 'boolean'],
            // SEO / media — nullable for v1
            'image' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'array'],
            'meta_keywords.*' => ['string', 'max:100'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],

        ];
    }
}
