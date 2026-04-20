<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('site_infos')) {
            return;
        }

        if (!Schema::hasColumn('site_infos', 'theme_vars')) {
            Schema::table('site_infos', function (Blueprint $table) {
                $table->json('theme_vars')->nullable()->after('logo_path');
            });
        }

        $defaults = [
            'font_default' => '"Poppins"',
            'font_primary' => '"Poppins"',
            'color_default' => '#444444',
            'color_primary' => '#00205f',
            'color_primary_dark' => '#2e87d3',
            'color_secondary' => '#00205f',
            'color_tertiary' => '#D52B1E',
            'color_white' => '#FFFFFF',
            'color_light' => '#f1f1f1',
            'color_dark' => '#002037',
            'regular_shadow' => '0 .5rem 1rem rgba(0,0,0,.15)!important',
            'scroll_behavior' => 'smooth',
            'bs_link_color' => '#00205f',
            'swiper_navigation_color' => '#00205f',
        ];

        DB::table('site_infos')
            ->whereNull('theme_vars')
            ->update(['theme_vars' => json_encode($defaults)]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_infos') || !Schema::hasColumn('site_infos', 'theme_vars')) {
            return;
        }

        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn('theme_vars');
        });
    }
};

