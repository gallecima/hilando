<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('menu_groups', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('icono')->nullable();
        $table->integer('orden')->default(0);
        $table->timestamps();
    });

    Schema::table('menus', function (Blueprint $table) {
        $table->foreignId('menu_group_id')->nullable()->constrained('menu_groups');
        $table->integer('orden')->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_groups');
    }
};
