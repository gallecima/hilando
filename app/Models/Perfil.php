<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Perfil extends Model
{
    protected $table = 'perfiles';
    protected $fillable = ['nombre'];

    public function menus()
    {
        return $this->belongsToMany(\App\Models\Menu::class);
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
    
    
}