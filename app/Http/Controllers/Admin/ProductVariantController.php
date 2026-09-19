<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $this->validated($request);

        $variant = $product->variants()->create($data);
        $variant->attributeValues()->sync(array_filter((array) $request->input('attribute_value_ids', [])));

        return redirect()->route('admin.products.edit', $product)->with('success', 'Variant added.');
    }

    public function update(Request $request, ProductVariant $productVariant)
    {
        $data = $this->validated($request, $productVariant->id);
        $productVariant->update($data);
        $productVariant->attributeValues()->sync(array_filter((array) $request->input('attribute_value_ids', [])));

        return redirect()->route('admin.products.edit', $productVariant->product_id)->with('success', 'Variant updated.');
    }

    public function destroy(ProductVariant $productVariant)
    {
        $productId = $productVariant->product_id;
        $productVariant->delete();

        return redirect()->route('admin.products.edit', $productId)->with('success', 'Variant deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'.($ignoreId ? ",{$ignoreId}" : '')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;

        return $data;
    }
}
