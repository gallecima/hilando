<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_billing_data', function (Blueprint $table) {
            $table->string('invoice_type', 1)->default('C')->after('tax_status');
        });
    }

    public function down(): void
    {
        Schema::table('customer_billing_data', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
        });
    }
};
