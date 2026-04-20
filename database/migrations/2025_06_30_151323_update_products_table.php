<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->unique()->after('name');
            $table->string('sku')->unique()->nullable()->after('slug');
            $table->text('description')->nullable()->after('sku');
            $table->text('short_description')->nullable()->after('description');
            $table->decimal('price', 10, 2)->after('short_description');
            $table->integer('stock')->after('price');
            $table->boolean('is_active')->default(1)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'slug', 'sku',
                'description', 'short_description',
                'price', 'stock', 'is_active'
            ]);
        });
    }
};
