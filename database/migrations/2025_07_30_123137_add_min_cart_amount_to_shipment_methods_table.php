<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('shipment_methods', function (Blueprint $table) {
        $table->decimal('min_cart_amount', 10, 2)->nullable()->after('amount');
    });
}

public function down()
{
    Schema::table('shipment_methods', function (Blueprint $table) {
        $table->dropColumn('min_cart_amount');
    });
}
};
