<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('delay')->nullable();

            $table->enum('discount_type', ['amount', 'percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();

            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locality_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('weight_limit', 10, 2)->nullable();
            $table->decimal('height_limit', 10, 2)->nullable();
            $table->decimal('width_limit', 10, 2)->nullable();
            $table->decimal('length_limit', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_methods');
    }
};