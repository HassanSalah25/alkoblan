<?php

namespace App\Http\Requests\Api;

class OrderCreateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_token' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:50'],
            'customer.company' => ['nullable', 'string', 'max:255'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.city' => ['nullable', 'string', 'max:255'],
            'customer.country' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,bank_transfer,online'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->input('cart_token')) && empty($this->input('items'))) {
                $validator->errors()->add('cart_token', 'Either cart_token or items must be provided.');
            }
        });
    }
}
