<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                             // Nombre visible
            $table->string('slug')->unique();                   // Identificador interno (mercadopago, transferencia)
            $table->string('type')->default('manual');          // manual, mercadopago, etc.
            $table->json('config')->nullable();                 // JSON con configuración específica
            $table->text('instructions')->nullable();           // Instrucciones visibles para el cliente
            $table->boolean('active')->default(true);           // Habilitado o no
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
