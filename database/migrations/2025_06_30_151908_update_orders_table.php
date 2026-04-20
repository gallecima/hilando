<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained('customers')->onDelete('cascade');
            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled'])->after('customer_id');
            $table->decimal('total', 10, 2)->after('status');
            $table->text('shipping_address')->after('total');
            $table->json('billing_data_json')->after('shipping_address');
            $table->text('notes')->nullable()->after('billing_data_json');
            $table->foreignId('coupon_id')->nullable()->after('notes')->constrained('discount_coupons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['customer_id', 'status', 'total', 'shipping_address', 'billing_data_json', 'notes', 'coupon_id']);
        });
    }
};
