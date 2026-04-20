<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slider extends Model
{
    protected $fillable = ['nombre', 'slug', 'activo'];

    protected static function booted()
    {
        static::creating(function ($slider) {
            $slider->slug = Str::slug($slider->nombre);
        });

        static::updating(function ($slider) {
            $slider->slug = Str::slug($slider->nombre);
        });
    }

    public function images()
    {
        return $this->hasMany(SliderImage::class);
    }
}