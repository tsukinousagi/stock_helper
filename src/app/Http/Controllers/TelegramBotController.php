<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Log;

class TelegramBotController extends Controller
{
    //
    public function webhook(Request $request) {
        $updates = Telegram::getWebhookUpdates();
        Log::info(__CLASS__ . ':' . __FUNCTION__);
        Log::info($updates);
        return 'ok';
    }
}
