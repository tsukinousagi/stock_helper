<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Log;

class TelegramBotController extends Controller
{
    //
    public function webhook(Request $request) {
        Log::info(__CLASS__ . ':' . __FUNCTION__);
        $updates = Telegram::getWebhookUpdates();
        Log::info($updates);
        $response = Telegram::commandsHandler(false, ['timeout' => 30]);
        Log::info($response);
        return 'ok';
    }
}
