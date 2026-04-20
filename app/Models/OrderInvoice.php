<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderInvoice extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'title',
        'number',
        'status',
        'issued_at',
        'file_path',
        'external_url',
        'meta',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'meta'      => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
