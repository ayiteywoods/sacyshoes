<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => HomeSection::KEY_HERO,
                'name' => 'Hero section',
                'eyebrow' => 'Heels, Flats & More',
                'title' => 'Step into',
                'title_highlight' => 'something beautiful.',
                'body' => 'Premium footwear curated for every occasion. Delivered across Ghana.',
                'primary_label' => 'Shop From All Products',
                'primary_url' => '/shop',
                'secondary_label' => 'New Arrivals',
                'secondary_url' => '/shop',
                'image_path' => 'images/brand/hero2.jpeg',
                'sort_order' => 1,
            ],
            [
                'key' => HomeSection::KEY_FREE_DELIVERY,
                'name' => 'Free delivery banner',
                'body' => 'Free delivery on orders over {currency_symbol} {threshold} — Delivered across Ghana',
                'sort_order' => 2,
            ],
            [
                'key' => HomeSection::KEY_SHOP_CATEGORY,
                'name' => 'Shop by category',
                'eyebrow' => 'Collections',
                'title' => 'Shop by Category',
                'body' => 'Find your perfect pair',
                'primary_label' => 'View All',
                'primary_url' => '/shop',
                'sort_order' => 3,
            ],
            [
                'key' => HomeSection::KEY_CTA,
                'name' => 'Call to action',
                'eyebrow' => 'Limited Drop',
                'title' => 'Upgrade your wardrobe with a new pair today.',
                'body' => 'Explore premium styles curated for every occasion — fast delivery across Ghana.',
                'primary_label' => 'Shop All Products',
                'primary_url' => '/shop',
                'secondary_label' => 'New Arrivals',
                'secondary_url' => '/shop',
                'sort_order' => 4,
            ],
            [
                'key' => HomeSection::KEY_NEW_ARRIVALS,
                'name' => 'New arrivals',
                'eyebrow' => 'Just Dropped',
                'title' => 'New Arrivals',
                'body' => 'Shop the latest styles from SACYSHOES.',
                'primary_label' => 'View Collection',
                'primary_url' => '/shop',
                'sort_order' => 5,
            ],
            [
                'key' => HomeSection::KEY_TESTIMONIALS_HEADER,
                'name' => 'Testimonials header',
                'eyebrow' => 'Reviews',
                'title' => 'What our customers say',
                'sort_order' => 6,
            ],
            [
                'key' => HomeSection::KEY_DELIVERY_NOTICE,
                'name' => 'Delivery notice',
                'title' => 'Delivery Information',
                'body' => 'Delivery fee is paid directly to the dispatch rider upon arrival. Fee varies by location across Ghana.',
                'sort_order' => 7,
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::query()->updateOrCreate(
                ['key' => $section['key']],
                array_merge(['is_active' => true], $section),
            );
        }

        $testimonials = [
            [
                'quote' => 'The quality of shoes from SACYSHOES is amazing. Great customer service and fast delivery. I always come back!',
                'author_name' => 'Sheila M.',
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'quote' => 'I have bought shoes here multiple times and can attest the goods are of the best quality. Highly recommended.',
                'author_name' => 'Charity E.',
                'rating' => 5,
                'sort_order' => 2,
            ],
            [
                'quote' => 'Best shoe vendor in Ghana. Customer service is superb and the shoes are always exactly as pictured.',
                'author_name' => 'Alishia Y.',
                'rating' => 5,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::query()->updateOrCreate(
                [
                    'author_name' => $testimonial['author_name'],
                    'quote' => $testimonial['quote'],
                ],
                array_merge(['is_active' => true], $testimonial),
            );
        }
    }
}
