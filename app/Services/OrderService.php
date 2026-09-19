<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Framework-agnostic order creation logic, reusable from the JSON API and
 * any future session-based web checkout controller. Throws
 * \InvalidArgumentException for bad input (line-item validation failures).
 */
class OrderService
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function createFromCart(Cart $cart, array $customer, string $paymentMethod, ?string $notes = null): Order
    {
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty.');
        }

        $rawItems = $cart->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'quantity' => $item->quantity,
        ])->all();

        $order = $this->createOrder($rawItems, $customer, $paymentMethod, $notes);

        $this->cartService->clear($cart);

        return $order;
    }

    public function createFromItems(array $items, array $customer, string $paymentMethod, ?string $notes = null): Order
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('No items provided for order.');
        }

        return $this->createOrder($items, $customer, $paymentMethod, $notes);
    }

    /**
     * @param  array<int, array{product_id:int, product_variant_id?:int|null, quantity:int}>  $rawItems
     */
    protected function createOrder(array $rawItems, array $customer, string $paymentMethod, ?string $notes): Order
    {
        $lines = [];

        foreach ($rawItems as $raw) {
            $quantity = max(1, (int) ($raw['quantity'] ?? 1));

            $product = Product::query()->find($raw['product_id'] ?? null);
            if (! $product || ! $product->is_active) {
                throw new \InvalidArgumentException("Product #{$raw['product_id']} not found or unavailable.");
            }

            if ($product->stock_status !== 'in_stock') {
                throw new \InvalidArgumentException("Product \"{$product->name}\" is currently out of stock.");
            }

            if ($quantity < $product->min_order_qty) {
                throw new \InvalidArgumentException("Minimum order quantity for \"{$product->name}\" is {$product->min_order_qty}.");
            }

            $variant = null;
            if (! empty($raw['product_variant_id'])) {
                $variant = ProductVariant::query()->find($raw['product_variant_id']);
                if (! $variant || $variant->product_id !== $product->id || ! $variant->is_active) {
                    throw new \InvalidArgumentException('Selected variant not found or unavailable.');
                }
            }

            // Always re-derive the price server-side, never trust the client.
            $unitPrice = (float) ($variant ? $variant->effective_price : $product->effective_price);

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'variant_label' => $variant?->label,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($unitPrice * $quantity, 2),
            ];
        }

        $subtotal = round(array_sum(array_column($lines, 'total')), 2);
        $taxRate = (float) (Setting::get('tax_rate', 15) ?: 15);
        $tax = round($subtotal * ($taxRate / 100), 2);
        $shipping = 0;
        $discount = 0;
        $total = round($subtotal - $discount + $tax + $shipping, 2);

        return DB::transaction(function () use ($lines, $customer, $paymentMethod, $notes, $subtotal, $tax, $shipping, $discount, $total) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $customer['user_id'] ?? null,
                'status' => 'pending',
                'currency' => 'SAR',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $total,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'customer_company' => $customer['company'] ?? null,
                'customer_address' => $customer['address'] ?? null,
                'customer_city' => $customer['city'] ?? null,
                'customer_country' => $customer['country'] ?? 'Saudi Arabia',
                'notes' => $notes,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create($line);
            }

            return $order;
        });
    }
}
