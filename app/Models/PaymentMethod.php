<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public const TYPES = ['mercadopago', 'transferencia', 'contraentrega'];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'config',
        'instructions',
        'active',
    ];

    protected $casts = [
        'config' => 'array',
        'active' => 'boolean',
    ];
}