<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramChatid extends Model
{
    //
    protected $table = 'telegram_chatid';
    protected $fillable = ['chat_id', 'username', 'note'];
}
