<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    
    // database/migrations/xxxx_xx_xx_add_name_and_image_to_cart_items_table.php
public function up()
{
    Schema::table('cart_items', function (Blueprint $table) {
        $table->string('name')->nullable()->after('product_id');
        $table->string('image')->nullable()->after('name');
    });
}


};
