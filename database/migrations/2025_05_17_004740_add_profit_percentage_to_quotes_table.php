<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'profit_percentage')) {
                $table->decimal('profit_percentage', 8, 2)->nullable()->default(0.00)->after('exchange_rate_promedio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) { // Debe ser la tabla 'quotes'
            if (Schema::hasColumn('quotes', 'profit_percentage')) {
                $table->dropColumn('profit_percentage');
            }
        });
    }
};
