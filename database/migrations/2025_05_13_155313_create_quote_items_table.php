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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id(); // ID único del ítem de cotización

            // Relación con la cotización principal
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade'); // A qué cotización pertenece. Si se borra la cotización, se borran sus ítems.

            // Relación con el producto (puede ser nulo si es un producto manual)
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null'); // A qué producto del catálogo se refiere. Si se borra el producto, este campo se vuelve nulo.

            // Campos para productos manuales (si product_id es nulo)
            $table->string('manual_product_name')->nullable(); // Nombre del producto si se añade manualmente
            $table->string('manual_product_unit')->nullable(); // Unidad de medida para producto manual
            $table->decimal('manual_product_cost', 15, 2)->nullable(); // Costo para producto manual

            // Detalles del ítem
            $table->decimal('quantity', 15, 2); // Cantidad del producto/servicio
            $table->decimal('cost', 15, 2);      // Costo unitario del producto/servicio en el momento de la cotización (puede ser del catálogo o manual)

            // Para el cálculo de precio con tasas
            $table->enum('price_calculation_method', ['promedio', 'bcv'])->nullable(); // Método de cálculo del precio (Promedio, BCV)
            $table->decimal('applied_rate_value', 15, 4)->nullable(); // Valor de la tasa (Promedio o BCV) que se usó

            $table->decimal('price', 15, 2);     // Precio unitario final del producto/servicio en la cotización
            $table->decimal('line_total', 15, 2); // Total por línea (quantity * price)

            $table->string('estimated_delivery_time')->nullable(); // Tiempo de entrega estimado para este ítem

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
