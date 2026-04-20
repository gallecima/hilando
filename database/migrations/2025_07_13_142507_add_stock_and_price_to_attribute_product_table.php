<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attribute_product', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('attribute_value_id');
            $table->decimal('price', 10, 2)->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('attribute_product', function (Blueprint $table) {
            $table->dropColumn(['stock', 'price']);
        });
    }
};