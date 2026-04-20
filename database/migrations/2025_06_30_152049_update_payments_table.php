<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->after('id')->constrained('orders')->onDelete('cascade');
            $table->string('method')->after('order_id');
            $table->enum('status', ['pending', 'completed', 'failed'])->after('method');
            $table->decimal('amount', 10, 2)->after('status');
            $table->string('transaction_id')->nullable()->after('amount');
            $table->json('payment_data')->nullable()->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'method', 'status', 'amount', 'transaction_id', 'payment_data']);
        });
    }
};
