<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'status',
        'amount',
        'transaction_id',
        'payment_data',
        'payment_method_id',
    ];

    protected $casts = [
        'payment_data' => 'array',
    ];

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}