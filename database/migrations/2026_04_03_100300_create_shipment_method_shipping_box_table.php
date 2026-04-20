<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_method_shipping_box', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_method_id')->constrained('shipment_methods')->cascadeOnDelete();
            $table->foreignId('shipping_box_id')->constrained('shipping_boxes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shipment_method_id', 'shipping_box_id'], 'shipment_method_box_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_method_shipping_box');
    }
};
