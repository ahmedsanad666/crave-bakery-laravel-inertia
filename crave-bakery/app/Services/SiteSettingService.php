<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SiteSettingService
{
    /**
     * @var list<string>
     */
    public const SETTING_KEYS = [
        'name',
        'overview',
        'tagline',
        'about',
        'hero_title',
        'hero_description',
        'hero_image',
        'hero_rating',
        'hero_rating_description',
        'story_title',
        'story_content',
        'since_year',
        'logo',
        'favicon',
        'email',
        'phone',
        'address',
        'social_links',
        'theme_palette',
        'font_heading',
        'font_body',
        'seo_title_template',
        'seo_meta_description',
        'seo_meta_keywords',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        SiteSetting::set($key, $value);
    }

    /**
     * @return list<array{id: string, label: string, colors: array<string, string>}>
     */
    public function palettes(): array
    {
        return array_values(config('theme.palettes', []));
    }

    /**
     * @return array{heading: list<string>, body: list<string>}
     */
    public function fonts(): array
    {
        return [
            'heading' => array_values(config('theme.fonts.heading', [])),
            'body' => array_values(config('theme.fonts.body', [])),
        ];
    }

    /**
     * @return array{id: string, label: string, colors: array<string, string>}
     */
    public function resolvePalette(?string $id = null): array
    {
        $palettes = $this->palettes();
        $id = $id ?: (string) static::get('theme_palette', config('theme.default_palette'));

        foreach ($palettes as $palette) {
            if (($palette['id'] ?? null) === $id) {
                return $palette;
            }
        }

        return $palettes[0] ?? [
            'id' => 'artisanal_warmth',
            'label' => 'Artisanal Warmth',
            'colors' => [
                'primary' => '#E8572A',
                'accent' => '#3D1A0E',
                'page_bg' => '#FDF6EE',
                'cards' => '#FFFFFF',
                'text' => '#1A1A1A',
            ],
            'tokens' => [],
        ];
    }

    /**
     * CSS custom properties for first-paint / runtime theming.
     *
     * @return array<string, string>
     */
    public function themeCssProperties(): array
    {
        $theme = $this->publicPayload()['theme'];
        $properties = [];

        foreach ($theme['palette']['tokens'] ?? [] as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }

            $properties['--color-'.$key] = $value;
        }

        $heading = $theme['font_heading'] ?: (string) config('theme.default_font_heading');
        $body = $theme['font_body'] ?: (string) config('theme.default_font_body');

        $properties['--font-heading'] = "'{$heading}'";
        $properties['--font-body'] = "'{$body}'";

        return $properties;
    }

    public function googleFontsHref(?string $heading = null, ?string $body = null): string
    {
        $theme = $this->publicPayload()['theme'];
        $heading = $heading ?: ($theme['font_heading'] ?: (string) config('theme.default_font_heading'));
        $body = $body ?: ($theme['font_body'] ?: (string) config('theme.default_font_body'));

        $family = static function (string $name, string $weights): string {
            return 'family='.str_replace(' ', '+', $name).':wght@'.$weights;
        };

        return 'https://fonts.googleapis.com/css2?'
            .$family($heading, '400;600;700')
            .'&'
            .$family($body, '400;500;600;700')
            .'&display=swap';
    }

    /**
     * @return array<string, mixed>
     */
    public function allForAdmin(): array
    {
        $settings = $this->rawSettings();
        $palette = $this->resolvePalette($settings['theme_palette'] ?? null);

        return [
            'settings' => $settings,
            'palettes' => $this->palettes(),
            'fonts' => $this->fonts(),
            'resolved_palette' => $palette,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(
        array $data,
        ?UploadedFile $logo = null,
        ?UploadedFile $favicon = null,
        ?UploadedFile $heroImage = null,
    ): void {
        DB::transaction(function () use ($data, $logo, $favicon, $heroImage) {
            if ($logo) {
                $this->replaceImage('logo', $logo);
            }

            if ($favicon) {
                $this->replaceImage('favicon', $favicon);
            }

            if ($heroImage) {
                $this->replaceImage('hero_image', $heroImage);
            }

            $writable = [
                'name',
                'overview',
                'hero_title',
                'hero_description',
                'hero_rating',
                'hero_rating_description',
                'story_title',
                'story_content',
                'since_year',
                'email',
                'phone',
                'address',
                'social_links',
                'theme_palette',
                'font_heading',
                'font_body',
                'seo_title_template',
                'seo_meta_description',
                'seo_meta_keywords',
            ];

            foreach ($writable as $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }

                $value = $data[$key];

                if ($key === 'social_links' && is_array($value)) {
                    static::set($key, [
                        'facebook' => $value['facebook'] ?? null,
                        'instagram' => $value['instagram'] ?? null,
                        'twitter' => $value['twitter'] ?? null,
                        'youtube' => $value['youtube'] ?? null,
                    ]);

                    continue;
                }

                if ($key === 'seo_meta_keywords') {
                    static::set($key, $this->normalizeKeywords($value));

                    continue;
                }

                if ($key === 'hero_rating') {
                    $rating = is_numeric($value) ? (float) $value : 3.5;
                    static::set($key, max(0, min(5, round($rating, 1))));

                    continue;
                }

                static::set($key, $value === null ? '' : $value);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        $settings = $this->rawSettings();
        $palette = $this->resolvePalette($settings['theme_palette'] ?? null);

        return [
            'site_name' => $settings['name'] ?: 'Crave Bakery',
            'name' => $settings['name'] ?: 'Crave Bakery',
            'overview' => $settings['overview'],
            'tagline' => $settings['tagline'],
            'about' => $settings['about'],
            'hero_title' => $settings['hero_title'],
            'hero_description' => $settings['hero_description'],
            'hero_image' => Product::toPublicUrl($settings['hero_image']),
            'hero_rating' => $settings['hero_rating'],
            'hero_rating_description' => $settings['hero_rating_description'],
            'story_title' => $settings['story_title'],
            'story_content' => $settings['story_content'],
            'since_year' => $settings['since_year'],
            'logo' => Product::toPublicUrl($settings['logo']),
            'favicon' => Product::toPublicUrl($settings['favicon']),
            'email' => $settings['email'],
            'phone' => $settings['phone'],
            'address' => $settings['address'],
            'social_links' => $settings['social_links'],
            'theme' => [
                'palette_id' => $palette['id'],
                'palette' => $palette,
                'font_heading' => $settings['font_heading'],
                'font_body' => $settings['font_body'],
            ],
            'seo' => [
                'title_template' => $settings['seo_title_template'],
                'meta_description' => $settings['seo_meta_description'],
                'meta_keywords' => $settings['seo_meta_keywords'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rawSettings(): array
    {
        $social = static::get('social_links', []);
        if (! is_array($social)) {
            $social = [];
        }

        $keywords = static::get('seo_meta_keywords', []);
        if (! is_array($keywords)) {
            $keywords = $this->normalizeKeywords($keywords);
        }

        $overview = static::get('overview');
        if (blank($overview)) {
            $overview = static::get('about') ?: static::get('tagline');
        }

        return [
            'name' => (string) (static::get('name') ?? ''),
            'overview' => (string) ($overview ?? ''),
            'tagline' => (string) (static::get('tagline') ?? ''),
            'about' => (string) (static::get('about') ?? ''),
            'hero_title' => (string) (static::get('hero_title') ?? ''),
            'hero_description' => (string) (static::get('hero_description') ?? ''),
            'hero_image' => static::get('hero_image'),
            'hero_rating' => (float) (static::get('hero_rating') ?: 3.5),
            'hero_rating_description' => (string) (static::get('hero_rating_description') ?? ''),
            'story_title' => (string) (static::get('story_title') ?? ''),
            'story_content' => (string) (static::get('story_content') ?? ''),
            'since_year' => (int) (static::get('since_year') ?: 1999),
            'logo' => static::get('logo'),
            'favicon' => static::get('favicon'),
            'email' => (string) (static::get('email') ?? ''),
            'phone' => (string) (static::get('phone') ?? ''),
            'address' => (string) (static::get('address') ?? ''),
            'social_links' => [
                'facebook' => $social['facebook'] ?? null,
                'instagram' => $social['instagram'] ?? null,
                'twitter' => $social['twitter'] ?? null,
                'youtube' => $social['youtube'] ?? null,
            ],
            'theme_palette' => (string) (static::get('theme_palette') ?? config('theme.default_palette')),
            'font_heading' => (string) (static::get('font_heading') ?? config('theme.default_font_heading')),
            'font_body' => (string) (static::get('font_body') ?? config('theme.default_font_body')),
            'seo_title_template' => (string) (static::get('seo_title_template') ?? '%site_name% | %tagline%'),
            'seo_meta_description' => (string) (static::get('seo_meta_description') ?? ''),
            'seo_meta_keywords' => array_values($keywords),
        ];
    }

    private function replaceImage(string $key, UploadedFile $file): void
    {
        $previous = static::get($key);
        $path = $file->store('settings', 'public');
        static::set($key, $path);

        if (is_string($previous) && $previous !== '' && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }
    }

    /**
     * @return list<string>
     */
    private function normalizeKeywords(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                $value,
            )));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[,]+/', $value) ?: [],
        )));
    }
}
