<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', SiteSetting::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $paletteIds = collect(config('theme.palettes', []))->pluck('id')->all();
        $headingFonts = config('theme.fonts.heading', []);
        $bodyFonts = config('theme.fonts.body', []);

        return [
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string', 'max:2000'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:2000'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'hero_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'hero_rating_description' => ['nullable', 'string', 'max:500'],
            'story_title' => ['nullable', 'string', 'max:255'],
            'story_content' => ['nullable', 'string', 'max:10000'],
            'since_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'social_links.facebook' => ['nullable', 'url', 'max:500'],
            'social_links.instagram' => ['nullable', 'url', 'max:500'],
            'social_links.twitter' => ['nullable', 'url', 'max:500'],
            'social_links.youtube' => ['nullable', 'url', 'max:500'],
            'theme_palette' => ['required', 'string', Rule::in($paletteIds)],
            'font_heading' => ['required', 'string', Rule::in($headingFonts)],
            'font_body' => ['required', 'string', Rule::in($bodyFonts)],
            'seo_title_template' => ['nullable', 'string', 'max:255'],
            'seo_meta_description' => ['nullable', 'string', 'max:320'],
            'seo_meta_keywords' => ['nullable'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('seo_meta_keywords') && is_string($this->input('seo_meta_keywords'))) {
            $keywords = array_values(array_filter(array_map(
                'trim',
                preg_split('/[,]+/', $this->input('seo_meta_keywords')) ?: [],
            )));
            $this->merge(['seo_meta_keywords' => $keywords]);
        }

        foreach (['facebook', 'instagram', 'twitter', 'youtube'] as $network) {
            $key = "social_links.{$network}";
            if ($this->input($key) === '') {
                $this->merge([
                    'social_links' => array_merge(
                        $this->input('social_links', []),
                        [$network => null],
                    ),
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsPayload(): array
    {
        return $this->safe()->except(['logo', 'favicon', 'hero_image']);
    }
}
