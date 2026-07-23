<?php

namespace Database\Seeders;

use App\Services\SiteSettingService;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'name' => 'Crave Bakery',
            'tagline' => 'Artisanal warmth in every bite',
            'about' => 'Artisan bakery crafting fresh pastries, cakes, and breads daily with premium ingredients.',
            'overview' => 'Artisan bakery crafting fresh pastries, cakes, and breads daily with premium ingredients.',
            'hero_title' => 'Baking Smiles, One Pastry At A Time',
            'hero_description' => 'Experience the warmth of our ovens delivered straight to your heart. Artisanal craftsmanship meets neighborhood comfort.',
            'hero_image' => null,
            'hero_rating' => 3.5,
            'hero_rating_description' => 'The best croissant in the city, hands down!',
            'story_title' => 'The Heart of Our Bakery',
            'story_content' => "For over three decades, Crave Bakery has been the aromatic heartbeat of our neighborhood. What started as a small family dream has blossomed into a destination for those who appreciate the patient art of slow-fermented dough and the golden crunch of a perfect crust.\n\nWe believe that good bread takes time. Our bakers arrive when the city still sleeps, hand-shaping every loaf and tempering every batch of chocolate to ensure that the warmth you feel in every bite is as authentic as the ingredients we source from local artisans.",
            'since_year' => 1999,
            'email' => 'hello@cravebakery.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Baker Street, Sweetville, CA 90210',
            'logo' => null,
            'favicon' => null,
            'social_links' => [
                'facebook' => null,
                'instagram' => null,
                'twitter' => null,
                'youtube' => null,
            ],
            'theme_palette' => 'artisanal_warmth',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Inter',
            'seo_title_template' => '%site_name% | %tagline%',
            'seo_meta_description' => 'Crave Bakery offers the finest artisanal breads, decadent pastries, and custom cakes in the city. Freshly baked every morning with locally sourced ingredients.',
            'seo_meta_keywords' => ['bakery', 'artisan bread', 'pastries', 'cakes', 'crave bakery'],
        ];

        foreach ($settings as $key => $value) {
            SiteSettingService::set($key, $value);
        }
    }
}
