<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasStockPriceToAttributesTable extends Migration
{
    public function up()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->boolean('has_stock_price')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('has_stock_price');
        });
    }
}