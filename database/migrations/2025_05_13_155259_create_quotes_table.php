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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id(); // ID único de la cotización
            $table->string('quote_number')->unique(); // Número de cotización (Ej: COT-2025-0001), único

            // Relaciones (Llaves Foráneas)
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // A qué cliente pertenece. Si se borra el cliente, se borran sus cotizaciones.
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');   // Qué usuario (vendedor) la creó. Restringir borrado de usuario si tiene cotizaciones.

            // Fechas importantes
            $table->date('issue_date'); // Fecha de emisión de la cotización
            $table->date('expiry_date'); // Fecha de validez de la oferta

            // Campos para los montos
            $table->decimal('subtotal', 15, 2)->default(0); // Subtotal antes de descuentos e impuestos
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable(); // Tipo de descuento global: porcentaje o fijo
            $table->decimal('discount_value', 15, 2)->nullable(); // Valor del descuento (ej: 10 para 10% o 50 para 50 USD)
            $table->decimal('discount_amount', 15, 2)->default(0); // Monto total del descuento aplicado
            $table->decimal('tax_percentage', 5, 2)->default(16.00); // Porcentaje de impuesto (ej: 16.00 para 16%)
            $table->decimal('tax_amount', 15, 2)->default(0); // Monto total del impuesto
            $table->decimal('total', 15, 2)->default(0); // Gran total de la cotización

            // Información adicional
            $table->text('terms_and_conditions')->nullable(); // Términos y condiciones
            $table->text('notes_to_client')->nullable();      // Notas visibles para el cliente
            $table->text('internal_notes')->nullable();       // Notas internas solo para el equipo

            // Estado y Moneda
            $table->string('status')->default('Borrador'); // Estados: Borrador, Enviada, Aceptada, Rechazada, Expirada, Cancelada
            $table->string('base_currency', 3)->default('USD'); // Moneda base de la cotización (ej: USD)
            $table->decimal('exchange_rate_bcv', 15, 4)->nullable(); // Tasa BCV al momento de cotizar (si aplica)
            $table->decimal('exchange_rate_promedio', 15, 4)->nullable(); // Tasa Promedio al momento de cotizar (si aplica)

            // Para el guardado automático y versionado simple
            $table->json('auto_save_data')->nullable(); // Para guardar el estado intermedio de la cotización
            // $table->unsignedInteger('version')->default(1); // Para un versionado más robusto (opcional)
            // $table->foreignId('parent_quote_id')->nullable()->constrained('quotes')->onDelete('set null'); // Si se duplica o basa en otra

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
