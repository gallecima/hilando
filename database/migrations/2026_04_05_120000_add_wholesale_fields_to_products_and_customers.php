<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_wholesaler')->default(false)->after('is_active');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('wholesale_price', 10, 2)->nullable()->after('base_price');
            $table->unsignedInteger('wholesale_min_quantity')->nullable()->after('wholesale_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'wholesale_min_quantity']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_wholesaler');
        });
    }
};
