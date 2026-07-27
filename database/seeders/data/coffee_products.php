<?php

/**
 * Curated Coffee Ship catalog data for ProductSeeder.
 *
 * @return array{
 *     images: list<string>,
 *     products: list<array{
 *         name: string,
 *         slug: string,
 *         short_description: string,
 *         description: string,
 *         regular_price: float,
 *         sale_price: ?float,
 *         sku: string,
 *         category_slugs: list<string>,
 *         image_indexes: list<int>,
 *         is_featured: bool,
 *         stock_quantity: int,
 *         stock_status: string,
 *         roast: string,
 *         grind: string,
 *         bag_size: string
 *     }>
 * }
 */
return (static function (): array {
    $images = [
        'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80',
        'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
        'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
        'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80',
        'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&q=80',
        'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=800&q=80',
        'https://images.unsplash.com/photo-1498804103079-a6351b050096?w=800&q=80',
        'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80',
        'https://images.unsplash.com/photo-1510590337019-5ef8d3d32116?w=800&q=80',
        'https://images.unsplash.com/photo-1610889556528-9a770639fc2f?w=800&q=80',
        'https://images.unsplash.com/photo-1611854779393-1b2da9d400fe?w=800&q=80',
        'https://images.unsplash.com/photo-1459755486867-b55449bb39ff?w=800&q=80',
        'https://images.unsplash.com/photo-1497935586351-b67a49eebaae?w=800&q=80',
        'https://images.unsplash.com/photo-1524350870554-ef9cda8d4b6a?w=800&q=80',
        'https://images.unsplash.com/photo-1442512595331-e89e7383650e?w=800&q=80',
        'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=800&q=80',
        'https://images.unsplash.com/photo-1485808191679-5f86510681cf?w=800&q=80',
        'https://images.unsplash.com/photo-1521302080334-4bebac2763a5?w=800&q=80',
        'https://images.unsplash.com/photo-1556742393-d75f468bfcb0?w=800&q=80',
        'https://images.unsplash.com/photo-1541167760491-1628856ab772?w=800&q=80',
    ];

    $roasts = ['Light', 'Medium', 'Medium-Dark', 'Dark'];
    $grinds = ['Whole Bean', 'Espresso', 'Filter', 'French Press', 'Cold Brew'];
    $bagSizes = ['250g', '500g', '1kg'];

    $catalog = [
        ['Ethiopia Yirgacheffe', 'Floral and citrus-forward washed Ethiopian with jasmine and bergamot.', ['single-origin', 'coffee-beans-grounds'], 24.50],
        ['Guatemala Antigua', 'Cocoa, caramel, and gentle spice from volcanic Antigua soils.', ['pacific-coffees', 'single-origin'], 22.00],
        ['Colombia Supremo Huila', 'Balanced sweetness with red apple and brown sugar notes.', ['single-origin', 'coffee-beans-grounds'], 21.50],
        ['Brazil Santos Bourbon', 'Nutty, chocolatey classic for espresso and milk drinks.', ['espresso', 'blends'], 18.90],
        ['Kenya AA Nyeri', 'Bright blackcurrant acidity with wine-like depth.', ['single-origin', 'pacific-coffees'], 28.00],
        ['Sumatra Mandheling', 'Earthy, full-bodied, low-acid Indonesian favourite.', ['single-origin', 'coffee-beans-grounds'], 20.50],
        ['Costa Rica Tarrazu', 'Clean honey sweetness and crisp citrus finish.', ['single-origin', 'pacific-coffees'], 23.00],
        ['Rwanda Bourbon', 'Silky body with red fruit and cocoa nib.', ['single-origin'], 25.00],
        ['Peru Cajamarca Organic', 'Smooth organic cup with almond and milk chocolate.', ['single-origin', 'coffee-beans-grounds'], 19.50],
        ['Honduras Copan', 'Stone fruit sweetness and soft caramel.', ['single-origin'], 20.00],
        ['House Espresso Blend', 'Our signature espresso — chocolate, hazelnut, lasting crema.', ['espresso', 'blends'], 19.00],
        ['Morning Roast Blend', 'Bright, easy everyday blend for drip and pour-over.', ['blends', 'coffee-beans-grounds'], 16.50],
        ['Midnight Dark Blend', 'Bold dark roast with smoked cocoa and molasses.', ['blends', 'espresso'], 17.50],
        ['Pacific Sunrise Blend', 'Island-inspired blend with tropical fruit and honey.', ['pacific-coffees', 'blends'], 21.00],
        ['Barista Milk Blend', 'Crafted for lattes and cappuccinos — sweet and stable.', ['espresso', 'blends'], 18.00],
        ['AeroPress Everyday', 'Medium roast tuned for AeroPress clarity and body.', ['aeropress', 'coffee-beans-grounds'], 17.00],
        ['AeroPress Competition Lot', 'Limited experimental lot roasted for AeroPress recipes.', ['aeropress', 'single-origin'], 29.00],
        ['Pour-Over Dripper Set', 'Ceramic V60-style dripper, filters, and server for clean cups.', ['equipment', 'gift-sets'], 42.00],
        ['Manual Burr Grinder', 'Stepless ceramic burr grinder for travel and home brewing.', ['equipment'], 55.00],
        ['Gooseneck Kettle 1L', 'Precision pour kettle with temperature-ready spout.', ['equipment'], 48.00],
        ['Cold Brew Concentrate Vanilla', 'Smooth vanilla-kissed concentrate — dilute 1:2 over ice.', ['cold-brew', 'instant-concentrates'], 14.99],
        ['Classic Cold Brew Kit', 'Coarse grind coffee, brew bag, and recipe card.', ['cold-brew', 'gift-sets'], 32.00],
        ['Vanilla Cold Brew Concentrate', 'Dessert-like concentrate with real vanilla bean.', ['instant-concentrates', 'cold-brew'], 15.50],
        ['Classic Instant Specialty', 'Freeze-dried specialty coffee — café quality in seconds.', ['instant-concentrates'], 12.00],
        ['Espresso Gift Box', 'Espresso blend, demitasse duo, and tasting card.', ['gift-sets', 'espresso'], 58.00],
        ['Explorer Gift Set', 'Three 250g single origins from Africa, LatAm, and Asia.', ['gift-sets', 'single-origin'], 64.00],
        ['AeroPress Go Kit', 'Travel AeroPress with scoop, filters, and 250g beans.', ['aeropress', 'gift-sets', 'equipment'], 72.00],
        ['Panama Geisha Micro-Lot', 'Ultra-floral Geisha with jasmine tea and bergamot.', ['single-origin', 'pacific-coffees'], 48.00],
        ['Yemen Mocha Mattari', 'Wild berry, wine, and spice — historic mocha profile.', ['single-origin'], 36.00],
        ['India Monsoon Malabar', 'Monsoon-processed low-acid beans, spicy and bold.', ['espresso', 'single-origin'], 22.50],
        ['Mexico Chiapas', 'Gentle chocolate and soft citrus from Chiapas highlands.', ['single-origin', 'pacific-coffees'], 19.00],
        ['Nicaragua Jinotega', 'Caramel sweetness with citrus lift.', ['single-origin'], 20.50],
        ['El Salvador Pacamara', 'Big bean, tropical fruit, and floral sweetness.', ['single-origin'], 27.00],
        ['Uganda Bugisu AA', 'Winey fruit and cocoa from Mount Elgon.', ['single-origin'], 18.50],
        ['Tanzania Peaberry', 'Bright peaberry cups with black tea and lemon.', ['single-origin', 'pacific-coffees'], 24.00],
        ['Decaf Swiss Water Colombia', 'Chemical-free decaf that keeps chocolate sweetness.', ['blends', 'coffee-beans-grounds'], 21.00],
        ['Office Drip Bulk 1kg', 'Reliable medium roast for office drip machines.', ['blends', 'coffee-beans-grounds'], 34.00],
        ['French Press Coarse Grind', 'Pre-ground coarse for immersion brewing.', ['coffee-beans-grounds'], 16.00],
        ['Espresso Fine Grind', 'Freshly ground for home espresso machines.', ['espresso', 'coffee-beans-grounds'], 18.50],
        ['Filter Medium Grind', 'Balanced grind for drip and pour-over.', ['coffee-beans-grounds'], 17.00],
        ['Reusable Metal Filter', 'Stainless mesh filter for pour-over and AeroPress.', ['equipment'], 14.00],
        ['Digital Brew Scale', '0.1g precision scale with timer for recipe control.', ['equipment'], 39.00],
        ['Double-Wall Travel Mug', 'Keeps coffee hot for hours — leak-resistant lid.', ['equipment', 'gift-sets'], 28.00],
        ['Latte Art Pitcher 350ml', 'Stainless milk pitcher for microfoam practice.', ['equipment'], 16.50],
        ['Tamper 58mm', 'Weighted espresso tamper with flat base.', ['equipment', 'espresso'], 24.00],
        ['Coffee Subscription Starter', 'First month of rotating single origins (3×250g).', ['gift-sets', 'single-origin'], 55.00],
        ['Holiday Blend Limited', 'Seasonal spice-friendly blend with dried fruit notes.', ['blends', 'gift-sets'], 19.50],
        ['Iced Coffee Ready Brew', 'Pre-brewed chilled coffee — pour over ice and serve.', ['cold-brew', 'instant-concentrates'], 11.00],
        ['Mocha Fudge Syrup Pairing', 'Café-style mocha syrup to pair with espresso blends.', ['gift-sets'], 9.50],
        ['Barista Toolkit Bundle', 'Scale, thermometer, and cloth set for home baristas.', ['equipment', 'gift-sets'], 68.00],
    ];

    // Expand to 100 with lot/altitude variants of the curated cores.
    $variants = [
        'Reserve Lot', 'High Altitude', 'Microlot', 'Estate Selection',
        'Small Batch', 'Roaster\'s Choice', 'Seasonal Lot', 'Export Grade',
        'Washed Process', 'Natural Process',
    ];

    $products = [];
    $index = 0;

    while (count($products) < 100) {
        $base = $catalog[$index % count($catalog)];
        $round = intdiv($index, count($catalog));
        $name = $base[0];
        if ($round > 0) {
            $name .= ' '.$variants[($index + $round) % count($variants)];
            if ($round > 1) {
                $name .= ' #'.$round;
            }
        }

        $slug = \Illuminate\Support\Str::slug($name);
        // Ensure unique slug if collision
        $slugBase = $slug;
        $n = 2;
        $existing = array_column($products, 'slug');
        while (in_array($slug, $existing, true)) {
            $slug = $slugBase.'-'.$n;
            $n++;
        }

        $price = round($base[3] * (1 + ($round * 0.04) + (($index % 5) * 0.01)), 2);
        $onSale = $index % 7 === 0;
        $featured = $index < 20;
        $stock = match (true) {
            $index % 23 === 0 => 0,
            $index % 11 === 0 => random_int(2, 7),
            default => random_int(15, 120),
        };

        $imgPrimary = $index % count($images);
        $imgSecondary = ($index + 3) % count($images);

        $short = $base[1];
        $description = $short.' Roasted in small batches at Coffee Ship and packed within 48 hours. '
            .'Store cool and airtight; grind just before brewing for the fullest aroma. '
            .'Cupping notes and brew recipes are included on every bag.';

        $products[] = [
            'name' => $name,
            'slug' => $slug,
            'short_description' => $short,
            'description' => $description,
            'regular_price' => $price,
            'sale_price' => $onSale ? round($price * 0.85, 2) : null,
            'sku' => 'CS-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
            'category_slugs' => $base[2],
            'image_indexes' => [$imgPrimary, $imgSecondary],
            'is_featured' => $featured,
            'stock_quantity' => $stock,
            'stock_status' => $stock === 0 ? 'out_of_stock' : 'in_stock',
            'roast' => $roasts[$index % count($roasts)],
            'grind' => $grinds[$index % count($grinds)],
            'bag_size' => $bagSizes[$index % count($bagSizes)],
        ];

        $index++;
    }

    return [
        'images' => $images,
        'products' => $products,
    ];
})();
