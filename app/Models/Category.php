<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'order',
        'image',
        'icon',
        'parent_id',
        'is_active',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }    

    public function ancestors(): Collection
    {
        $ancestors = collect();
        $node = $this->parent;
        while ($node) {
            $ancestors->prepend($node);
            $node = $node->parent;
        }
        return $ancestors;
}    
}
