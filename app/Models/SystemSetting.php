<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Puedes quitar HasFactory si no vas a usar factories para este modelo
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    // Si no vas a usar factories para este modelo, puedes quitar la línea de abajo
    // use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Indica si el modelo debe tener timestamps.
     * Desactivamos created_at y updated_at si no los incluimos en la migración.
     *
     * @var bool
     */
    public $timestamps = false; // Establécelo en true si descomentaste $table->timestamps(); en la migración
}