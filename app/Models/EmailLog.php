<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
  protected $fillable = [
    'key','order_id','to','subject','transport','ok','error','context'
  ];

  protected $casts = [
    'ok'      => 'boolean',
    'context' => 'array',
  ];
}