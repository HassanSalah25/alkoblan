<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\FamousClient;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;

class HomepageService
{
    protected const CONTENT_BLOCK_KEYS = ['home_about', 'home_quality', 'home_cta'];

    public function aggregate(): array
    {
        $blocks = ContentBlock::whereIn('key', self::CONTENT_BLOCK_KEYS)
            ->where('is_active', true)
            ->with('image')
            ->get()
            ->keyBy('key');

        return [
            'hero_slides' => HeroSlide::active()->with(['imageDesktop', 'imageMobile'])->get(),
            'content_blocks' => [
                'home_about' => $blocks->get('home_about'),
                'home_quality' => $blocks->get('home_quality'),
                'home_cta' => $blocks->get('home_cta'),
            ],
            'testimonials' => Testimonial::active()->with('image')->get(),
            'famous_clients' => FamousClient::active()->with('logo')->get(),
            'featured_categories' => ProductCategory::active()->topLevel()->featured()->with('image')->get(),
            'featured_products' => Product::active()->featured()
                ->with(['category', 'images.media'])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ];
    }
}
