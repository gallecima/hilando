<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShipmentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'delay',
        'discount_type',
        'discount_value',
        'country_id',
        'province_id',
        'locality_id',
        'shipping_point_id',
        'allow_nearby_match',
        'nearby_radius_km',
        'country_name',
        'province_name',
        'locality_name',
        'postal_code',
        'weight_limit',
        'height_limit',
        'width_limit',
        'length_limit',
        'min_cart_amount',
        'plugin_key',
        'is_pickup', 
        'is_active',
    ];

    // Relaciones con país, provincia y localidad
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

    public function shippingPoint()
    {
        return $this->belongsTo(ShippingPoint::class);
    }

    public function shippingBoxes()
    {
        return $this->belongsToMany(ShippingBox::class, 'shipment_method_shipping_box')
            ->withTimestamps();
    }

    // Accesor para mostrar el nombre completo de la zona
    public function getZoneNameAttribute()
    {
        $parts = [];

        if (!empty($this->locality_name)) {
            $parts[] = $this->locality_name;
        } elseif ($this->locality) {
            $parts[] = $this->locality->name;
        }

        if (!empty($this->province_name)) {
            $parts[] = $this->province_name;
        } elseif ($this->province) {
            $parts[] = $this->province->name;
        }

        if (!empty($this->country_name)) {
            $parts[] = $this->country_name;
        } elseif ($this->country) {
            $parts[] = $this->country->name;
        }

        if (!empty($this->postal_code)) {
            $parts[] = 'CP ' . $this->postal_code;
        }

        return implode(', ', array_values(array_filter($parts, fn ($v) => (string) $v !== '')));
    }

    public function scopeAvailable(Builder $q): Builder
    {
        $table = $q->getModel()->getTable(); // "shipment_methods"

        $q->where("$table.is_active", 1);

        return $q->where(function (Builder $w) use ($table) {
            // Métodos sin plugin (NULL o cadena vacía)
            $w->whereNull("$table.plugin_key")
              ->orWhere("$table.plugin_key", '=' , '');

            // Métodos con plugin: sólo si el plugin (slug) está activo
            if (
                Schema::hasTable('plugins') &&
                Schema::hasColumn('plugins', 'slug') &&
                Schema::hasColumn('plugins', 'is_active')
            ) {
                $w->orWhereExists(function ($sub) use ($table) {
                    $sub->select(DB::raw(1))
                        ->from('plugins')
                        ->whereColumn('plugins.slug', "$table.plugin_key")
                        ->where('plugins.is_active', 1);
                });
            }
            // Si no existe la tabla/columnas de plugins, NO agregamos más condiciones:
            // así, sólo quedan visibles los métodos sin plugin.
        });
    }
}
