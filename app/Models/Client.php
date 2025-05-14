<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importante para la relación

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'identifier',
        'address',
        'phone',
        'email',
        'contact_person',
        // 'client_type', // Si lo añades en la migración
        // 'zone',        // Si lo añades en la migración
    ];

    /**
     * Get all of the quotes for the Client.
     * Un cliente puede tener muchas cotizaciones.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}