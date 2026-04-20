<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'is_active',
        'completed_at',
        'notes',
    ];

    protected $dates = [
        'completed_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Total del carrito (puede calcularse en tiempo real)
     */
    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Verifica si el carrito ya fue completado (convertido en orden)
     */
    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }
}