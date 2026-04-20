<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained('customers')->onDelete('cascade');
            $table->string('title')->after('customer_id');
            $table->string('address_line')->after('title');
            $table->string('city')->after('address_line');
            $table->string('province')->after('city');
            $table->string('postal_code')->after('province');
            $table->string('country')->after('postal_code');
            $table->boolean('is_default')->default(0)->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'title', 'address_line', 'city', 'province', 'postal_code', 'country', 'is_default']);
        });
    }
};
