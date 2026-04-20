<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('cart_id')->after('id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->after('cart_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->after('product_id');
            $table->decimal('price', 10, 2)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['cart_id', 'product_id', 'quantity', 'price']);
        });
    }
};
