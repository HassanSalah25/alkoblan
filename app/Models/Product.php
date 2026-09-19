<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'name_ar', 'slug', 'source_url', 'sku', 'short_description', 'short_description_ar',
        'description', 'description_ar', 'specifications', 'technical_specifications',
        'price', 'sale_price', 'currency', 'stock_quantity', 'stock_status', 'min_order_qty',
        'is_featured', 'is_active', 'sort_order',
        'seo_title', 'seo_title_ar', 'seo_description', 'seo_description_ar', 'seo_keywords',
    ];

    protected $casts = [
        'specifications' => 'array',
        'technical_specifications' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'main');
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class)->where('type', 'gallery')->orderBy('sort_order');
    }

    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price ?: $this->price);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return ! is_null($this->sale_price) && (float) $this->sale_price < (float) $this->price;
    }
}
