<?php

// database/migrations/2025_08_25_000000_add_plugin_key_to_shipment_methods.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->string('plugin_key', 100)->nullable()->index()
                  ->after('is_active'); // ajustá la posición si querés
        });
    }

    public function down(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->dropColumn('plugin_key');
        });
    }
};