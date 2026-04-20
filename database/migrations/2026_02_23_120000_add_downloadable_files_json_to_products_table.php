<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'downloadable_files_json')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->json('downloadable_files_json')->nullable()->after('downloadable_file_path');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'downloadable_files_json')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('downloadable_files_json');
        });
    }
};
