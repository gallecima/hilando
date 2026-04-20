<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_infos', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_website')->nullable();
            $table->string('support_email')->nullable();
            $table->string('logo_path')->nullable(); // ruta en storage (disk configurable)
            $table->timestamps();
        });

        // Semilla mínima (fila única)
        DB::table('site_infos')->insert([
            'company_name'    => 'Tu Empresa S.A.',
            'company_address' => 'Av. Siempre Viva 742, Salta',
            'company_website' => 'https://tu-dominio.com',
            'support_email'   => 'soporte@tu-dominio.com',
            'logo_path'       => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_infos');
    }
};