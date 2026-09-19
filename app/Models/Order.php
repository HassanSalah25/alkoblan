<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status', 'currency', 'subtotal', 'discount', 'tax',
        'shipping', 'total', 'customer_name', 'customer_email', 'customer_phone',
        'customer_company', 'customer_address', 'customer_city', 'customer_country',
        'notes', 'payment_method', 'payment_status', 'placed_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'AK-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }
}
