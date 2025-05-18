<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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
        'internal_notes',
        'status',
        'base_currency',
        'exchange_rate_bcv',
        'exchange_rate_promedio',
        'profit_percentage',
        'auto_save_data',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'auto_save_data' => 'array',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate_bcv' => 'decimal:4',
        'exchange_rate_promedio' => 'decimal:4',
        'profit_percentage' => 'decimal:2',
    ];

    public const STATUS_BORRADOR = 'Borrador';
    public const STATUS_ENVIADA = 'Enviada';
    public const STATUS_ACEPTADA = 'Aceptada';
    public const STATUS_RECHAZADA = 'Rechazada';
    public const STATUS_EXPIRADA = 'Expirada';
    public const STATUS_CANCELADA = 'Cancelada';

    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_BORRADOR,
            self::STATUS_ENVIADA,
            self::STATUS_ACEPTADA,
            self::STATUS_RECHAZADA,
            self::STATUS_EXPIRADA,
            self::STATUS_CANCELADA,
        ];
    }
    
    public static function getStatusMap(): array
    {
        return [
            self::STATUS_BORRADOR => 'Borrador',
            self::STATUS_ENVIADA => 'Enviada',
            self::STATUS_ACEPTADA => 'Aceptada',
            self::STATUS_RECHAZADA => 'Rechazada',
            self::STATUS_EXPIRADA => 'Expirada',
            self::STATUS_CANCELADA => 'Cancelada',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(QuoteHistory::class)->orderBy('created_at', 'desc');
    }

    public function getStatusTextAttribute(): string
    {
        $statusMap = self::getStatusMap();
        return $statusMap[$this->status] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_BORRADOR => 'secondary',
            self::STATUS_ENVIADA => 'info',
            self::STATUS_ACEPTADA => 'success',
            self::STATUS_RECHAZADA => 'danger',
            self::STATUS_EXPIRADA => 'warning',
            self::STATUS_CANCELADA => 'dark',
            default => 'light',
        };
    }
}
