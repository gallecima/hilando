<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogPostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('bajada')->nullable();
            $table->text('descripcion');
            $table->date('fecha');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // autor
            $table->foreignId('blog_category_id')->constrained()->onDelete('cascade');
            $table->string('imagen_destacada')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
}