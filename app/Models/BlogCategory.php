<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'slug', 'activo'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
    }
}