<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 100)->unique();
            $table->string('provider')->nullable();
            $table->decimal('inner_length', 10, 2);
            $table->decimal('inner_width', 10, 2);
            $table->decimal('inner_height', 10, 2);
            $table->decimal('max_weight', 10, 2);
            $table->decimal('box_weight', 10, 2)->default(0);
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_boxes');
    }
};
