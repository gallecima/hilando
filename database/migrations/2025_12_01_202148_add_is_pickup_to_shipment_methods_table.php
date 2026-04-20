<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->boolean('is_pickup')
                ->default(false)
                ->after('plugin_key'); // o después de lo que te resulte más lógico
        });
    }

    public function down(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->dropColumn('is_pickup');
        });
    }
};