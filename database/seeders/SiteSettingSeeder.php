<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'name' => 'Coffee Ship',
            'overview' => 'Specialty coffee beans, blends, and brew gear shipped fresh from our roastery.',
            'tagline' => 'Freshly roasted. Carefully sourced. Delivered to your door.',
            'about' => 'Coffee Ship is a specialty coffee roaster and online shop. We partner with farms across Africa, Latin America, and the Pacific to bring transparent, seasonal lots to home baristas — plus the equipment and gifts that make brewing a daily ritual.',
            'hero_title' => 'Ekrandan Yayılan Mis Gibi Kahve Kokusu.',
            'hero_description' => 'Discover single origins, signature blends, and brew kits — roasted in small batches and shipped at peak freshness.',
            'hero_image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1200&q=80',
            'hero_rating' => '4.5',
            'hero_rating_description' => 'Loved by home baristas for freshness, clarity, and tasting notes that actually show up in the cup.',
            'story_title' => 'From farm lots to your morning cup',
            'story_content' => 'We roast mid-week, bag within hours, and ship fast so aroma survives the journey. Whether you brew espresso, pour-over, AeroPress, or cold brew, our catalog is built for flavour — not filler.',
            'story_image' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=1200&q=80',
            'since_year' => '2014',
            'logo' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=200&q=80',
            'favicon' => null,
            'email' => 'hello@coffeeship.com',
            'phone' => '+1 (555) 214-8800',
            'address' => '128 Roastery Lane, Portland, OR 97209',
            'social_links' => [
                'facebook' => 'https://facebook.com/coffeeship',
                'instagram' => 'https://instagram.com/coffeeship',
                'twitter' => 'https://twitter.com/coffeeship',
                'youtube' => 'https://youtube.com/@coffeeship',
            ],
            'seo_title_template' => '{page_title} | Coffee Ship',
            'seo_meta_description' => 'Shop specialty coffee beans, espresso blends, cold brew, and brew equipment at Coffee Ship.',
            'seo_meta_keywords' => 'coffee, specialty coffee, beans, espresso, cold brew, aeropress',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::set($key, $value);
        }
    }
}
