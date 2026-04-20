<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'slug',
        'bajada',
        'descripcion',
        'fecha',
        'imagen_destacada',
        'activo',
        'blog_category_id',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'blog_post_product')->withTimestamps();
    }
  
}
