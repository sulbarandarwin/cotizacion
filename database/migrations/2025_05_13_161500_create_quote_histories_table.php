<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_histories', function (Blueprint $table) {
            $table->id(); // ID único del registro de historial

            // Relación con la cotización principal
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade'); // A qué cotización pertenece este registro. Si se borra la cotización, se borra su historial.

            // Quién hizo el cambio
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Qué usuario realizó la acción. Nulo si es una acción del sistema o si el usuario se borra.

            $table->string('action'); // Descripción de la acción (Ej: "Cotización Creada", "Estado Cambiado a Enviada", "Ítem Añadido", "Descuento Aplicado")
            $table->text('details')->nullable(); // Detalles adicionales en formato JSON (Ej: valor anterior y nuevo valor, qué ítem se modificó)
            $table->timestamp('created_at')->useCurrent(); // Solo necesitamos created_at, que registrará cuándo ocurrió la acción. No necesitamos updated_at aquí.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_histories');
    }
};
