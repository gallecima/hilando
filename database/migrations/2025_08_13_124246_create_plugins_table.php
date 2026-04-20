<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('plugins', function (Blueprint $table) {
      $table->id();
      $table->string('name');        // Human name, ej: Hello World
      $table->string('slug')->unique(); // Unique key, ej: helloworld
      $table->string('version')->nullable();
      $table->boolean('is_installed')->default(false);
      $table->boolean('is_active')->default(false);
      $table->json('config')->nullable();    // message, contexts, etc
      $table->timestamp('installed_at')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('plugins');
  }
};