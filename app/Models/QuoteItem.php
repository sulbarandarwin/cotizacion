<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para relaciones "pertenece a"

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'manual_product_name',
        'manual_product_unit',
        'manual_product_cost',
        'quantity',
        'cost',
        'price_calculation_method',
        'applied_rate_value',
        'price',
        'line_total',
        'estimated_delivery_time',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'manual_product_cost' => 'decimal:2',
        'applied_rate_value' => 'decimal:4',
        'price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Obtener la cotización a la que pertenece este ítem.
     * Un ítem de cotización pertenece a una cotización.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Obtener el producto del catálogo al que se refiere este ítem (si aplica).
     * Un ítem de cotización puede pertenecer a un producto.
     */
    public function product(): BelongsTo
    {
        // Este product_id puede ser nulo, la relación lo manejará.
        return $this->belongsTo(Product::class);
    }
}