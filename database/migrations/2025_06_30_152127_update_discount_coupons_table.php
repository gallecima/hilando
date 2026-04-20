<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->string('code')->unique()->after('id');
            $table->string('description')->nullable()->after('code');
            $table->enum('discount_type', ['percentage', 'fixed'])->after('description');
            $table->decimal('discount_value', 10, 2)->after('discount_type');
            $table->integer('max_uses')->nullable()->after('discount_value');
            $table->integer('uses')->default(0)->after('max_uses');
            $table->date('valid_from')->nullable()->after('uses');
            $table->date('valid_until')->nullable()->after('valid_from');
            $table->boolean('is_active')->default(1)->after('valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'discount_type', 'discount_value', 'max_uses', 'uses', 'valid_from', 'valid_until', 'is_active']);
        });
    }
};
