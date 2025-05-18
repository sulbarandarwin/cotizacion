<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'manual_product_name',
        'manual_product_unit',
        // 'manual_product_cost', // El costo ya está en 'cost'
        'quantity',
        'cost',
        'price',
        'price_calculation_method',
        'applied_rate_value',
        'line_total',
        'estimated_delivery_time',
        'cost_applies_iva', // NUEVO CAMPO
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'applied_rate_value' => 'decimal:4',
        'line_total' => 'decimal:2',
        'cost_applies_iva' => 'boolean', // NUEVO CAST
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}