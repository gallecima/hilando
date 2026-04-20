<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slider_images', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('orden');
            $table->text('hero_text')->nullable()->after('hero_title');
            $table->json('cta_buttons')->nullable()->after('hero_text');
        });
    }

    public function down(): void
    {
        Schema::table('slider_images', function (Blueprint $table) {
            $table->dropColumn(['hero_title', 'hero_text', 'cta_buttons']);
        });
    }
};
