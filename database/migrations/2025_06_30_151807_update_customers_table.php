<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->string('password')->after('email');
            $table->string('phone')->nullable()->after('password');
            $table->string('document')->nullable()->after('phone');
            $table->boolean('is_active')->default(1)->after('document');
            $table->rememberToken()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'password', 'phone', 'document', 'is_active', 'remember_token']);
        });
    }
};
