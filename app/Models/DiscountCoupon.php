<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCoupon extends Model
{

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_uses',
        'uses',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $dates = ['valid_from', 'valid_until'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}