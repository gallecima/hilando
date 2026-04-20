<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->string('country_name')->nullable()->after('locality_id');
            $table->string('province_name')->nullable()->after('country_name');
            $table->string('locality_name')->nullable()->after('province_name');
            $table->string('postal_code', 20)->nullable()->after('locality_name');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'country_name',
                'province_name',
                'locality_name',
                'postal_code',
            ]);
        });
    }
};
