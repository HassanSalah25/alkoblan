<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\Request;

class ProductFileController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'media_id' => ['required', 'exists:media,id'],
            'type' => ['required', 'in:catalog,price_list,datasheet,installation_guide,certificate,other'],
            'title' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
        ]);

        $product->files()->create($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'File attached.');
    }

    public function destroy(ProductFile $productFile)
    {
        $product = $productFile->product;
        $productFile->delete();

        return redirect()->route('admin.products.edit', $product)->with('success', 'File removed.');
    }
}
