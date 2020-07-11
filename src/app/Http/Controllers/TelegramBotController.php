<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;

class TelegramBotController extends Controller
{
    //
    public function webhook(Request $request) {
        $updates = Telegram::getWebhookUpdates();
        
        return 'ok';
    }
}
