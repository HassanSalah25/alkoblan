<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_value');
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price ?: $this->price ?: $this->product->price);
    }

    public function getLabelAttribute(): string
    {
        return $this->attributeValues->pluck('value')->implode(' / ');
    }
}
