<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'has_stock_price',
    ];

    /**
     * Relación: Un atributo tiene muchos valores.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    // App\Models\Attribute.php

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }    
    
}
