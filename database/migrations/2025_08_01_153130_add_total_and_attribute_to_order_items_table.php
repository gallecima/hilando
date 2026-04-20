<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->nullable()->after('price');
            $table->unsignedBigInteger('attribute_value_id')->nullable()->after('product_id');

            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['attribute_value_id']);
            $table->dropColumn(['total', 'attribute_value_id']);
        });
    }
};