<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'shipment_method_id',
        'payment_method_id',
        'status',
        'subtotal',
        'discount',
        'shipping_cost',
        'shipping_discount',
        'total',
        'shipping_address',
        'billing_data_json',
        'notes',
        'coupon_id',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_data_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (empty($order->public_token)) {
                $order->public_token = Str::uuid()->toString(); // o Str::random(40)
            }
        });
    }    

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(OrderInvoice::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function shipmentMethod()
    {
        return $this->belongsTo(\App\Models\ShipmentMethod::class, 'shipment_method_id');
    }    

    public function coupon()
    {
        return $this->belongsTo(DiscountCoupon::class, 'coupon_id');
    }

    public function getPaymentAttribute()
    {
        return $this->payments->last(); // o ->first() si preferís el primero
    }  
       
    public function updateStatus()
    {
        $latestPayment = $this->payments()->latest()->first();
        $shipment = $this->shipment;

        if ($this->status === 'cancelled') {
            return; // no tocar si fue cancelado manualmente
        }

        if ($latestPayment?->status === 'failed' || !$latestPayment) {
            $this->status = 'pending';
        } elseif ($latestPayment->status === 'completed') {
            if ($shipment) {
                if ($shipment->status === 'delivered') {
                    $this->status = 'delivered';
                } elseif ($shipment->status === 'shipped') {
                    $this->status = 'shipped';
                } else {
                    $this->status = 'paid';
                }
            } else {
                $this->status = 'paid';
            }
        }

        $this->saveQuietly(); // evita loops con observers
    }    
}
