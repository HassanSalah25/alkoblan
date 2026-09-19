<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'media_id' => ['required', 'exists:media,id'],
            'type' => ['required', 'in:main,gallery,thumbnail,technical_drawing'],
        ]);

        $maxOrder = (int) $product->images()->max('sort_order');

        if ($data['type'] === 'main') {
            // Only one "main" image per product.
            $product->images()->where('type', 'main')->update(['type' => 'gallery']);
        }

        $product->images()->create([
            'media_id' => $data['media_id'],
            'type' => $data['type'],
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Image added.');
    }

    public function destroy(ProductImage $productImage)
    {
        $product = $productImage->product;
        $productImage->delete();

        return redirect()->route('admin.products.edit', $product)->with('success', 'Image removed.');
    }

    public function setMain(ProductImage $productImage)
    {
        $product = $productImage->product;
        $product->images()->where('type', 'main')->update(['type' => 'gallery']);
        $productImage->update(['type' => 'main']);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Main image updated.');
    }
}
