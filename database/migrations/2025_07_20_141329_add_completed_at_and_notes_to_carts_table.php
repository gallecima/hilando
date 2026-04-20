<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('is_active');
            $table->text('notes')->nullable()->after('completed_at');
        });
    }

};
