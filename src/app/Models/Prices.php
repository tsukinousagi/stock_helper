<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    //
    protected $table = 'prices';
    protected $fillable = ['code', 'd_date', 'price_open', 'price_high', 'price_low', 'price_close', 'volume'];
}
