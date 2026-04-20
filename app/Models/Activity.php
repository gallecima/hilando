<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'occurred_at',
        'user_id',
        'category',
        'type',
        'description',
        'subject_type',
        'subject_id',
        'meta',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta'        => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}



/*


activity()->log([
  'category'    => 'plataforma',
  'type'        => 'modificacion',
  'description' => 'Se actualizó el precio del Producto '.$product->id,
  'subject_type'=> \App\Models\Product::class,
  'subject_id'  => $product->id,
  'meta'        => ['old_price' => 12000, 'new_price' => 14500],
]);



*/