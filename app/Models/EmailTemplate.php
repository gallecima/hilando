<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
  protected $fillable = ['key','name','subject','body_html','enabled','options'];
  protected $casts = ['enabled'=>'bool','options'=>'array'];
}