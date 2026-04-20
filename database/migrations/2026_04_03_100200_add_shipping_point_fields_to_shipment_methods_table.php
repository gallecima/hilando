<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->foreignId('shipping_point_id')->nullable()->after('locality_id')->constrained('shipping_points')->nullOnDelete();
            $table->boolean('allow_nearby_match')->default(false)->after('shipping_point_id');
            $table->decimal('nearby_radius_km', 8, 2)->nullable()->after('allow_nearby_match');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_methods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_point_id');
            $table->dropColumn(['allow_nearby_match', 'nearby_radius_km']);
        });
    }
};
