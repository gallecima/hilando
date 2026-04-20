<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('order_id')->after('id')->constrained('orders')->onDelete('cascade');
            $table->string('tracking_number')->nullable()->after('order_id');
            $table->string('carrier')->nullable()->after('tracking_number');
            $table->enum('status', ['pending', 'shipped', 'delivered'])->after('carrier');
            $table->timestamp('shipped_at')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'tracking_number', 'carrier', 'status', 'shipped_at', 'delivered_at']);
        });
    }
};
