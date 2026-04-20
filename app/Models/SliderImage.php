<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SliderImage extends Model
{
    protected $fillable = ['slider_id', 'imagen', 'orden', 'hero_title', 'hero_text', 'cta_buttons'];

    protected $casts = [
        'cta_buttons' => 'array',
    ];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}
