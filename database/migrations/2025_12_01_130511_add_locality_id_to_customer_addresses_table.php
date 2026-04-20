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
        Schema::table('customer_addresses', function (Blueprint $table) {
            // Agregamos locality_id como FK opcional
            $table->foreignId('locality_id')
                  ->nullable()
                  ->after('country') // o donde te resulte más lógico
                  ->constrained('localities')
                  ->nullOnDelete(); // si se borra la localidad, pone locality_id en null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            // Primero dropeamos la FK y luego la columna
            $table->dropForeign(['locality_id']);
            $table->dropColumn('locality_id');
        });
    }
};