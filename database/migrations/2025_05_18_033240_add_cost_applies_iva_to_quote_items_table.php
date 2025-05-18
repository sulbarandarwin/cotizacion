<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) { // Tabla correcta: quote_items
            if (!Schema::hasColumn('quote_items', 'cost_applies_iva')) {
                $table->boolean('cost_applies_iva')->default(false)->after('applied_rate_value'); // O después del campo que prefieras
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            if (Schema::hasColumn('quote_items', 'cost_applies_iva')) {
                $table->dropColumn('cost_applies_iva');
            }
        });
    }
};
