<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingBox extends Model
{
    protected $fillable = [
        'name',
        'code',
        'provider',
        'inner_length',
        'inner_width',
        'inner_height',
        'max_weight',
        'box_weight',
        'priority',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'inner_length' => 'float',
        'inner_width' => 'float',
        'inner_height' => 'float',
        'max_weight' => 'float',
        'box_weight' => 'float',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function shipmentMethods()
    {
        return $this->belongsToMany(ShipmentMethod::class, 'shipment_method_shipping_box')
            ->withTimestamps();
    }

    public function getInnerVolumeAttribute(): float
    {
        return round(
            (float) $this->inner_length * (float) $this->inner_width * (float) $this->inner_height,
            2
        );
    }
}
