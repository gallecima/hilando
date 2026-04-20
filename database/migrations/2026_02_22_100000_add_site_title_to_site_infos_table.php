<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('site_title')->nullable()->after('id');
        });

        DB::table('site_infos')
            ->whereNull('site_title')
            ->update(['site_title' => 'Bilingual Treasure']);
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn('site_title');
        });
    }
};

