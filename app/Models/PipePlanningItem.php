<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipePlanningItem extends Model
{
    protected $fillable = [
        'analysis_id', 'product_id', 'product_variant_id', 'category', 'water', 'diameter_mm',
        'secondary_diameter_mm', 'base_quantity', 'quantity', 'unit', 'quantity_status',
        'match_status', 'source', 'derivation', 'notes',
    ];

    protected $casts = [
        'derivation' => 'array',
        'base_quantity' => 'float',
        'quantity' => 'float',
    ];

    public function analysis()
    {
        return $this->belongsTo(PipePlanningAnalysis::class, 'analysis_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
