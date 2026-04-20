<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPoint extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'address_line',
        'country_id',
        'province_id',
        'locality_id',
        'country_name',
        'province_name',
        'locality_name',
        'postal_code',
        'latitude',
        'longitude',
        'service_radius_km',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'service_radius_km' => 'float',
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function shipmentMethods()
    {
        return $this->hasMany(ShipmentMethod::class);
    }

    public function getZoneNameAttribute(): string
    {
        $parts = array_filter([
            $this->locality_name ?: $this->locality?->name,
            $this->province_name ?: $this->province?->name,
            $this->country_name ?: $this->country?->name,
        ]);

        if ($this->postal_code) {
            $parts[] = 'CP ' . $this->postal_code;
        }

        return implode(', ', $parts);
    }
}
