<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    //
    protected $table = 'subscriptions';
    protected $fillable = ['telegram_chat_id', 'goods', 'goods_trace_type', 'exprie_at'];
}
