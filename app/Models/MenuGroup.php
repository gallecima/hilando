<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    protected $fillable = ['nombre', 'icono', 'orden'];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    
}