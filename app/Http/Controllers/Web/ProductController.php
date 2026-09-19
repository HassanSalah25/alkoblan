<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->active()
            ->with([
                'category', 'images.media', 'files.media',
                'attributeValues.attribute',
                'variants.attributeValues',
            ])
            ->firstOrFail();

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('mainImage.media')
            ->limit(4)->get();

        return view('pages.product-detail', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
