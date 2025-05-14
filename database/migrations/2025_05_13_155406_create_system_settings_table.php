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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id(); // ID único para el registro de configuración (opcional, pero estándar)
            $table->string('key')->unique(); // El nombre de la configuración (ej: 'iva_rate', 'company_name'), debe ser único
            $table->text('value')->nullable(); // El valor de la configuración, puede ser texto largo y nulo
            // No necesitamos timestamps aquí si cada configuración se establece una vez o se actualiza directamente.
            // Si quieres rastrear cuándo se actualizó una configuración, puedes añadir:
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
