<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CartAddItemRequest;
use App\Http\Requests\Api\CartUpdateItemRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function show(Request $request)
    {
        $cart = $this->cartService->resolveCart($this->cartToken($request), $this->authUserId($request));

        return $this->successResponse($this->cartService->summary($cart), 'Cart retrieved successfully.');
    }

    public function store(CartAddItemRequest $request)
    {
        $cart = $this->cartService->resolveCart($this->cartToken($request), $this->authUserId($request));

        try {
            $this->cartService->addItem(
                $cart,
                (int) $request->input('product_id'),
                $request->input('product_variant_id') ? (int) $request->input('product_variant_id') : null,
                (int) $request->input('quantity', 1)
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }

        return $this->successResponse($this->cartService->summary($cart), 'Item added to cart successfully.', 201);
    }

    public function update(CartUpdateItemRequest $request, int $cartItemId)
    {
        $cart = $this->cartService->resolveCart($this->cartToken($request), $this->authUserId($request));

        try {
            $this->cartService->updateItem($cart, $cartItemId, (int) $request->input('quantity'));
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), [], 404);
        }

        return $this->successResponse($this->cartService->summary($cart), 'Cart updated successfully.');
    }

    public function destroyItem(Request $request, int $cartItemId)
    {
        $cart = $this->cartService->resolveCart($this->cartToken($request), $this->authUserId($request));

        try {
            $this->cartService->removeItem($cart, $cartItemId);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), [], 404);
        }

        return $this->successResponse($this->cartService->summary($cart), 'Item removed from cart successfully.');
    }

    public function clear(Request $request)
    {
        $cart = $this->cartService->resolveCart($this->cartToken($request), $this->authUserId($request));

        $this->cartService->clear($cart);

        return $this->successResponse($this->cartService->summary($cart), 'Cart cleared successfully.');
    }

    protected function cartToken(Request $request): ?string
    {
        return $request->header('X-Cart-Token') ?: $request->input('cart_token');
    }

    protected function authUserId(Request $request): ?int
    {
        return $request->user('sanctum')?->id;
    }
}
