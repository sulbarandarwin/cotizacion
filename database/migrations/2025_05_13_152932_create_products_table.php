<?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('products', function (Blueprint $table) {
                    $table->id();
                    $table->string('code')->unique()->comment('Código único del producto (SKU)');
                    $table->string('name')->comment('Nombre del producto');
                    // La columna 'description' se añade en una migración posterior
                    $table->decimal('cost', 10, 2)->comment('Costo del producto');
                    $table->string('unit_of_measure')->nullable()->comment('Unidad de medida (ej: Pza, Kg, Lt, Caja)');
                    $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
                    $table->string('tax_type')->default('Gravado')->comment('Tipo de impuesto: Gravado, Exento, No Sujeto');
                    $table->string('image_path')->nullable()->comment('Ruta a la imagen del producto');
                    $table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('products');
            }
        };
        