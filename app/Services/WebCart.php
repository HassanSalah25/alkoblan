<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Server-side cart for the Blade storefront, backed by the carts/cart_items
 * tables and keyed by the Laravel session id (or user_id when authenticated).
 * This is intentionally separate from the stateless API cart (which uses a
 * client-supplied cart_token) since the web storefront already has session
 * cookies available.
 */
class WebCart
{
    public static function current(bool $create = true): ?Cart
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $query = Cart::query();
        $cart = $userId
            ? $query->where('user_id', $userId)->first()
            : Cart::where('session_id', $sessionId)->whereNull('user_id')->first();

        if (! $cart && $create) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'currency' => 'SAR',
            ]);
        }

        return $cart?->load('items.product.mainImage.media', 'items.variant.attributeValues');
    }

    public static function add(int $productId, ?int $variantId, int $qty = 1): Cart
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::find($variantId) : null;
        $unitPrice = $variant?->effective_price ?? $product->effective_price;

        $cart = static::current();

        $item = $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($item) {
            $item->increment('quantity', max(1, $qty));
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => max(1, $qty),
                'unit_price' => $unitPrice,
            ]);
        }

        return static::current();
    }

    public static function update(int $itemId, int $qty): ?Cart
    {
        $cart = static::current(false);
        if (! $cart) {
            return null;
        }
        $item = $cart->items()->where('id', $itemId)->first();
        if (! $item) {
            return $cart;
        }
        if ($qty < 1) {
            $item->delete();
        } else {
            $item->update(['quantity' => $qty]);
        }

        return static::current();
    }

    public static function remove(int $itemId): ?Cart
    {
        $cart = static::current(false);
        $cart?->items()->where('id', $itemId)->delete();

        return static::current(false);
    }

    public static function clear(): void
    {
        $cart = static::current(false);
        $cart?->items()->delete();
    }

    public static function count(): int
    {
        $cart = static::current(false);

        return $cart ? (int) $cart->items->sum('quantity') : 0;
    }

    public static function summary(): array
    {
        $cart = static::current(false);
        $items = $cart?->items ?? collect();

        $subtotal = (float) $items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $taxRate = (float) (setting('tax_rate', 15));
        $tax = round($subtotal * $taxRate / 100, 2);
        $shipping = 0.0;
        $total = $subtotal + $tax + $shipping;

        return [
            'cart' => $cart,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax_rate' => $taxRate,
            'shipping' => $shipping,
            'total' => $total,
        ];
    }
}
