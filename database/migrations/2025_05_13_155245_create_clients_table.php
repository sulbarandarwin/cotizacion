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
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // Columna ID autoincremental (llave primaria)
            $table->string('name'); // Nombre o Razón Social del cliente
            $table->string('identifier')->unique(); // RIF/Cédula del cliente, debe ser único
            $table->text('address')->nullable(); // Dirección, puede ser nula (opcional)
            $table->string('phone')->nullable(); // Teléfono, puede ser nulo (opcional)
            $table->string('email')->nullable()->unique(); // Email, opcional pero único si se ingresa
            $table->string('contact_person')->nullable(); // Persona de contacto, opcional
            // Campos opcionales que podrías necesitar en el futuro (descomenta si los necesitas):
            // $table->string('client_type')->nullable(); // Para clasificar clientes (Ej: Mayorista, Detal)
            // $table->string('zone')->nullable(); // Para zona geográfica
            // $table->foreignId('assigned_seller_id')->nullable()->constrained('users')->onDelete('set null'); // Si quieres asignar un vendedor específico
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};