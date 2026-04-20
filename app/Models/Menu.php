<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $fillable = ['nombre', 'grupo', 'ruta', 'icono', 'activo', 'orden', 'menu_group_id'];

    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'menu_perfil');
    }

    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }
}