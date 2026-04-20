<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // si existe la FK, la dropeamos primero
            try { $table->dropForeign(['customer_id']); } catch (\Throwable $e) {}

            // volverla nullable (requiere doctrine/dbal para change())
            $table->unsignedBigInteger('customer_id')->nullable()->change();

            // recrear FK opcionalmente
            try {
                $table->foreign('customer_id')
                      ->references('id')->on('customers')
                      ->nullOnDelete(); // o quitá esta línea si no querés FK
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try { $table->dropForeign(['customer_id']); } catch (\Throwable $e) {}
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};