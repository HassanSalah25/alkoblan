<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WebCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $summary = WebCart::summary();
        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('pages.checkout', $summary);
    }

    public function store(CheckoutRequest $request)
    {
        $summary = WebCart::summary();
        $items = $summary['items'];

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $order = DB::transaction(function () use ($request, $summary, $items) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'status' => 'pending',
                'currency' => 'SAR',
                'subtotal' => $summary['subtotal'],
                'discount' => 0,
                'tax' => $summary['tax'],
                'shipping' => $summary['shipping'],
                'total' => $summary['total'],
                'customer_name' => trim($request->first_name.' '.$request->last_name),
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'customer_company' => $request->company,
                'customer_address' => trim($request->address.' '.$request->district),
                'customer_city' => $request->city,
                'customer_country' => 'Saudi Arabia',
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => trans_field($item->product, 'name'),
                    'product_sku' => $item->product->sku,
                    'variant_label' => $item->variant?->label,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            return $order;
        });

        WebCart::clear();

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();

        return view('pages.checkout-success', ['order' => $order]);
    }
}
