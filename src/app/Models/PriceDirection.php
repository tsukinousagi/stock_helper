<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceDirection extends Model
{
    //
    protected $table = 'price_direction';
    protected $fillable = ['code', 'd_date', 'direction', 'last_changed'];
}
