<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion_electronica', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('domicilio');
            $table->string('cuit')->unique();
            $table->text('cert_crt')->nullable();
            $table->text('public_key')->nullable();
            $table->integer('punto_venta')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion_electronica');
    }
};
