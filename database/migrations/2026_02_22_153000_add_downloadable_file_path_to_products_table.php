<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'downloadable_file_path')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('downloadable_file_path')->nullable()->after('featured_image');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'downloadable_file_path')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('downloadable_file_path');
        });
    }
};
