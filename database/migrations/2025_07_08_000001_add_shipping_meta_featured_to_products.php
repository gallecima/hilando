<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Shipping info
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('is_digital')->default(false);

            // Status flags
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(false);

            // Meta fields
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // Featured image path
            $table->string('featured_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'height',
                'width',
                'length',
                'weight',
                'is_digital',
                'is_new',
                'is_featured',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'featured_image'
            ]);
        });
    }
};
