<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::table('attribute_product', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('image')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::table('attribute_product', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
