<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteHistory extends Model
{
    use HasFactory;

    /**
     * Los campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'quote_id',
        'user_id',
        'action',
        'details',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array', // Convierte el JSON de 'details' a array PHP y viceversa
    ];

    /**
     * Solo se usarán timestamps de 'created_at'.
     * No necesitamos 'updated_at' para registros de historial.
     */
    const UPDATED_AT = null;

    /**
     * Obtener la cotización a la que pertenece este registro de historial.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Obtener el usuario que realizó la acción (si aplica).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}