<?php

namespace App\Http\Requests\Api;

class CartUpdateItemRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:0'],
            'cart_token' => ['nullable', 'string'],
        ];
    }
}
