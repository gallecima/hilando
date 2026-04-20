<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Solo la creamos si no existe
            if (!Schema::hasColumn('shipments', 'shipping_data_json')) {
                $table->json('shipping_data_json')->nullable()->after('tracking_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'shipping_data_json')) {
                $table->dropColumn('shipping_data_json');
            }
        });
    }
};