<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturacionElectronica extends Model
{
    protected $table = 'facturacion_electronica';

    protected $fillable = [
        'razon_social',
        'domicilio',
        'cuit',
        'cert_crt',
        'public_key',
        'punto_venta',
    ];
}