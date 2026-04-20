<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('email_logs', function (Blueprint $t) {
      $t->id();
      $t->string('key', 100)->index();              // order_confirmed / payment_status_updated / shipment_status_updated
      $t->unsignedBigInteger('order_id')->nullable()->index();
      $t->string('to')->index();
      $t->string('subject')->nullable();
      $t->string('transport', 30)->nullable();      // plugin_smtp | laravel
      $t->boolean('ok')->default(false);
      $t->text('error')->nullable();
      $t->json('context')->nullable();              // extras render, deltas de estado, etc.
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('email_logs');
  }
};