<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para relaciones "pertenece a"
use Illuminate\Database\Eloquent\Relations\HasMany;   // Para relaciones "tiene muchos"
// Asegúrate que Carbon está importado si no lo está globalmente.
// use Carbon\Carbon;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'client_id',
        'user_id',
        'issue_date',
        'expiry_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total',
        'terms_and_conditions',
        'notes_to_client',
        'internal_notes', // Añadido según tu controlador
        'status',
        'base_currency',
        'exchange_rate_bcv',
        'exchange_rate_promedio',
        'auto_save_data',
        // 'version',
        // 'parent_quote_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date', // Convierte a objeto Carbon/Date
        'expiry_date' => 'date', // Convierte a objeto Carbon/Date
        'auto_save_data' => 'array', // Convierte el JSON a array PHP y viceversa
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate_bcv' => 'decimal:4',
        'exchange_rate_promedio' => 'decimal:4',
    ];

    /**
     * Obtener el cliente al que pertenece la cotización.
     * Una cotización pertenece a un cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtener el usuario (vendedor) que creó la cotización.
     * Una cotización pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener todos los ítems de la cotización.
     * Una cotización tiene muchos ítems.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Obtener el historial de cambios de la cotización.
     * Una cotización puede tener muchos registros de historial.
     */
    public function history(): HasMany
    {
        // Ordenar por el más reciente por defecto si es útil
        return $this->hasMany(QuoteHistory::class)->orderBy('created_at', 'desc');
    }

    // --- INICIO ACCESORS PARA ESTADO ---
    /**
     * Obtener el texto legible del estado de la cotización.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'Borrador' => 'Borrador',
            'Enviada' => 'Enviada',
            'Aceptada' => 'Aceptada',
            'Rechazada' => 'Rechazada',
            'Expirada' => 'Expirada',
            'Cancelada' => 'Cancelada',
            // Puedes añadir otros estados si los manejas
        ];
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Obtener la clase CSS para el badge del estado.
     *
     * @return string
     */
    public function getStatusClassAttribute(): string
    {
        $baseClass = 'badge ';
        $statusClasses = [
            'Borrador' => 'badge-secondary',
            'Enviada' => 'badge-info',
            'Aceptada' => 'badge-success',
            'Rechazada' => 'badge-danger',
            'Expirada' => 'badge-warning',
            'Cancelada' => 'badge-dark', // o badge-danger también
        ];
        return $baseClass . ($statusClasses[$this->status] ?? 'badge-light');
    }
    // --- FIN ACCESORS PARA ESTADO ---
}