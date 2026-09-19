<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('shop.index'), 'priority' => '0.9'],
            ['loc' => route('categories.index'), 'priority' => '0.8'],
            ['loc' => route('about'), 'priority' => '0.6'],
            ['loc' => route('company-profile'), 'priority' => '0.6'],
            ['loc' => route('blog.index'), 'priority' => '0.7'],
            ['loc' => route('contact'), 'priority' => '0.5'],
            ['loc' => route('faq.index'), 'priority' => '0.4'],
            ['loc' => route('careers.index'), 'priority' => '0.4'],
        ]);

        ProductCategory::active()->get()->each(fn ($c) => $urls->push(['loc' => route('shop.index', ['cat' => $c->slug]), 'priority' => '0.7']));
        Product::active()->get()->each(fn ($p) => $urls->push(['loc' => route('products.show', $p->slug), 'priority' => '0.6', 'lastmod' => $p->updated_at->toAtomString()]));
        BlogPost::published()->get()->each(fn ($b) => $urls->push(['loc' => route('blog.show', $b->slug), 'priority' => '0.5', 'lastmod' => $b->updated_at->toAtomString()]));
        Page::published()->get()->each(fn ($p) => $urls->push(['loc' => route('pages.show', $p->slug), 'priority' => '0.4', 'lastmod' => $p->updated_at->toAtomString()]));

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
