<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('shipment_method_id')->nullable()->after('order_id');
            $table->string('address')->nullable()->after('shipment_method_id');

            $table->foreign('shipment_method_id')->references('id')->on('shipment_methods')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['shipment_method_id']);
            $table->dropColumn(['shipment_method_id', 'address']);
        });
    }
};