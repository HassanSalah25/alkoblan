<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\OrderCreateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService, protected CartService $cartService)
    {
    }

    public function store(OrderCreateRequest $request)
    {
        $customer = $request->input('customer');
        $customer['user_id'] = $request->user('sanctum')?->id;

        $paymentMethod = $request->input('payment_method');
        $notes = $request->input('notes');

        try {
            if ($request->filled('cart_token')) {
                $cart = $this->cartService->resolveCart($request->input('cart_token'), $request->user('sanctum')?->id);
                $order = $this->orderService->createFromCart($cart, $customer, $paymentMethod, $notes);
            } else {
                $order = $this->orderService->createFromItems($request->input('items', []), $customer, $paymentMethod, $notes);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }

        $order->load('items');

        return $this->successResponse(new OrderResource($order), 'Order placed successfully.', 201);
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->first();

        if (! $order) {
            return $this->errorResponse('Order not found.', [], 404);
        }

        if ($order->user_id !== null) {
            $user = $request->user('sanctum');
            if (! $user || $user->id !== $order->user_id) {
                return $this->errorResponse('Order not found.', [], 404);
            }
        }

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }
}
