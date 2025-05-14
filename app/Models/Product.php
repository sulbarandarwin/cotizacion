<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'unit_of_measure',
        'cost',
        // 'description', // Si añades la descripción en la migración
        // 'category_id', // Si añades categorías
    ];

    // Aquí definiremos relaciones más adelante, por ejemplo, con categorías
    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }
}
