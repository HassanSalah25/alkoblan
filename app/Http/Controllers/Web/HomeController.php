<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContentBlock;
use App\Models\FamousClient;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'heroSlides' => HeroSlide::active()->with(['imageDesktop'])->get(),
            'featuredCategories' => ProductCategory::topLevel()->featured()->active()->get(),
            'featuredProducts' => Product::active()->featured()->with(['category', 'mainImage.media'])->orderBy('sort_order')->limit(8)->get(),
            'aboutBlock' => ContentBlock::find_key('home_about'),
            'ctaBlock' => ContentBlock::find_key('home_cta'),
            'latestPosts' => BlogPost::published()->with('category')->latest('published_at')->limit(3)->get(),
            'testimonials' => Testimonial::active()->get(),
            'famousClients' => FamousClient::active()->with('logo')->get(),
        ]);
    }
}
