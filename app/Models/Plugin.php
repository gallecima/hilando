<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name','slug','version','message','is_installed','is_active','config','installed_at'
    ];

    protected $casts = [
        'is_installed' => 'bool',
        'is_active'    => 'bool',
        'installed_at' => 'datetime',
        'config'       => 'array',
    ];
}