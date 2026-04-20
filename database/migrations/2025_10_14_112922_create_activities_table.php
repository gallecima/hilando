<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            // Cuándo ocurrió (puede diferir de created_at si importás eventos del pasado)
            $table->timestamp('occurred_at')->useCurrent();

            // Quién lo provocó (opcional)
            $table->foreignId('user_id')->nullable()
                  ->constrained()->nullOnDelete();

            // Clasificación
            $table->string('category', 32); // plataforma | administrativos | comerciales
            $table->string('type', 32)->nullable(); // alta | baja | modificacion | venta | emision_factura | envio | ...

            // Qué pasó (breve)
            $table->text('description');

            // Contexto del objeto afectado (Order, Product, etc.)
            $table->nullableMorphs('subject'); // subject_type, subject_id (ambos indexados)

            // Datos extra (diffs, importes, etc.)
            $table->json('meta')->nullable();

            // Trazabilidad
            $table->string('ip', 45)->nullable();         // IPv4/IPv6
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index(['category', 'type']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
