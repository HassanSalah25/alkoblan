<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Framework-agnostic cart logic, reusable from both the JSON API and any
 * future session-based web checkout controller. Throws \InvalidArgumentException
 * for bad input; callers translate that into whatever error shape they need.
 */
class CartService
{
    /**
     * Resolve (or create) the cart for this request. Authenticated users are
     * resolved by user_id; guests are resolved by the opaque cart_token they
     * hold in the `carts.session_id` column.
     */
    public function resolveCart(?string $cartToken, ?int $userId): Cart
    {
        if ($userId) {
            $cart = Cart::where('user_id', $userId)->first();
            if ($cart) {
                return $cart;
            }

            return Cart::create([
                'user_id' => $userId,
                'session_id' => $cartToken ?: (string) Str::uuid(),
                'currency' => 'SAR',
            ]);
        }

        if ($cartToken) {
            $cart = Cart::whereNull('user_id')->where('session_id', $cartToken)->first();
            if ($cart) {
                return $cart;
            }
        }

        return Cart::create([
            'session_id' => $cartToken ?: (string) Str::uuid(),
            'currency' => 'SAR',
        ]);
    }

    public function addItem(Cart $cart, int $productId, ?int $productVariantId, int $quantity = 1): CartItem
    {
        $quantity = max(1, $quantity);

        $product = Product::query()->find($productId);
        if (! $product || ! $product->is_active) {
            throw new \InvalidArgumentException('Product not found or unavailable.');
        }

        if ($product->stock_status !== 'in_stock') {
            throw new \InvalidArgumentException('Product is currently out of stock.');
        }

        if ($quantity < $product->min_order_qty) {
            throw new \InvalidArgumentException("Minimum order quantity for this product is {$product->min_order_qty}.");
        }

        $variant = null;
        if ($productVariantId) {
            $variant = ProductVariant::query()->find($productVariantId);
            if (! $variant || $variant->product_id !== $product->id || ! $variant->is_active) {
                throw new \InvalidArgumentException('Selected variant not found or unavailable.');
            }
        }

        $unitPrice = $variant ? $variant->effective_price : $product->effective_price;

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->save();

            return $existing;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function updateItem(Cart $cart, int $cartItemId, int $quantity): ?CartItem
    {
        $item = $cart->items()->where('id', $cartItemId)->first();
        if (! $item) {
            throw new \InvalidArgumentException('Cart item not found.');
        }

        if ($quantity < 1) {
            $item->delete();

            return null;
        }

        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem(Cart $cart, int $cartItemId): void
    {
        $item = $cart->items()->where('id', $cartItemId)->first();
        if (! $item) {
            throw new \InvalidArgumentException('Cart item not found.');
        }

        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Items + totals for the given cart, plus the cart_token the client
     * should persist and resend.
     */
    public function summary(Cart $cart): array
    {
        $cart->load(['items.product.images.media', 'items.variant.attributeValues']);

        $items = $cart->items->map(function (CartItem $item) {
            $product = $item->product;
            $image = null;
            if ($product) {
                $img = $product->images->firstWhere('type', 'main') ?? $product->images->firstWhere('type', 'gallery');
                $image = $img?->media?->url;
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $product?->name,
                'product_name_ar' => $product?->name_ar,
                'product_slug' => $product?->slug,
                'product_image' => $image,
                'variant_label' => $item->variant?->label,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'total' => (float) $item->total,
            ];
        })->values();

        $subtotal = (float) $items->sum('total');
        $taxRate = (float) (Setting::get('tax_rate', 15) ?: 15);
        $tax = round($subtotal * ($taxRate / 100), 2);
        $total = round($subtotal + $tax, 2);

        return [
            'cart_token' => $cart->session_id,
            'items' => $items,
            'items_count' => (int) $items->sum('quantity'),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $total,
            'currency' => $cart->currency,
        ];
    }
}
