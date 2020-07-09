<?php
/**
 * 記錄個股每分鐘價量的table
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricesMinute extends Model
{
    //
    protected $table = 'prices_minute';
    protected $fillable = ['code', 'd_date', 'price_open', 'price_high', 'price_low', 'price_close', 'volume'];
}
