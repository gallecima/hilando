<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::table('orders', function (Blueprint $table) {
    $table->string('name')->nullable()->after('customer_id');
    $table->string('email')->nullable()->after('name');
    $table->string('phone')->nullable()->after('email');
    $table->unsignedBigInteger('shipment_method_id')->nullable()->after('phone');

    $table->foreign('shipment_method_id')->references('id')->on('shipment_methods')->nullOnDelete();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
