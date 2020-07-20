<?php
/**
 * 處理Telegram Bot的API
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\RemoteUrlService;
use Telegram;


class TelegramBotAPIService {

    
    public function testTelegramBotAPI() {
        // 處理指令
        $response = Telegram::commandsHandler(false, ['timeout' => 30]);
//        var_dump($response);

        // 發送訊息
        /*
        $response = Telegram::sendMessage([
            'chat_id' => '718555290',
            'text' => 'orz',
        ]);
        var_dump($response);
        */
        return true;
    }
    
    /**
     * 開啟Telegram Bot的Webhook
     * @return boolean
     */
    public function setWebhook() {
        $response = Telegram::setWebhook([
            'url' => env('APP_URL') . 'telegram_me'
        ]);
        $tmp = $response->getDecodedBody();
        Log::info(json_encode($response));
        return $tmp;
    }

    /**
     * 關閉Telegram Bot的Webhook
     * @return boolean
     */
    public function deleteWebhook() {
        $response = Telegram::removeWebhook();
        $tmp = $response->getDecodedBody();
        Log::info(json_encode($response));
        return $tmp['ok'];
    }
    
    /**
     * 透過BOT發送訊息
     * @param string $chatid
     * @param string $text
     * @return unknown
     */
    public function sendMessageViaTelegramBot(string $chatid, string $text) {
        $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => $text,
        ]);
        return $response;
    }
}
