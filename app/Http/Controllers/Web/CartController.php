<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WebCart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('pages.cart', WebCart::summary());
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($data['product_id']);
        if (! $product->is_active || $product->stock_status === 'out_of_stock') {
            return response()->json(['success' => false, 'message' => 'هذا المنتج غير متوفر حالياً'], 422);
        }

        WebCart::add($data['product_id'], $data['variant_id'] ?? null, $data['quantity'] ?? 1);

        return response()->json([
            'success' => true,
            'message' => 'تمت الإضافة للسلة',
            'count' => WebCart::count(),
            'product_name' => trans_field($product, 'name'),
        ]);
    }

    public function update(Request $request, int $item)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:99']);
        WebCart::update($item, $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function remove(int $item)
    {
        WebCart::remove($item);

        return redirect()->route('cart.index');
    }

    public function clear()
    {
        WebCart::clear();

        return redirect()->route('cart.index');
    }
}
