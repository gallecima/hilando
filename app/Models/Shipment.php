<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'shipment_method_id',
        'address',
        'tracking_number',
        'carrier',
        'status',
        'shipped_at',
        'delivered_at',
        'shipping_data_json',
    ];

    protected $casts = [
        'address'            => 'array',
        'shipping_data_json' => 'array',
        'shipped_at'         => 'datetime',
        'delivered_at'       => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function method()
    {
        return $this->belongsTo(ShipmentMethod::class, 'shipment_method_id');
    }
}