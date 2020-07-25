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
        echo(json_encode($response) . PHP_EOL);
        
        $this->replyIfNotCommand($response);

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
    
    /**
     * 不是指令的話做出回應
     * @param unknown $response
     * @return boolean
     */
    public function replyIfNotCommand($response) {
        foreach($response as $v) {

            $check = false;
            $message = $v['message']['text'];

            // 檢查是不是用斜線開頭
            if (substr($message, 0, 1) <> '/') {
                $check = true;
            }

            // 檢查是不是有效指令
            if (!$check) {
                $check = $this->checkIsNotBotCommand($message);
            }

            // 發訊息回應
            if ($check) {
                $chatid = $v['message']['chat']['id'];
                $ret = $this->sendMessageViaTelegramBot($chatid, '輸入錯誤，請使用 /start 指令觀看說明');
            }
        }
        return true;
    }
    
    /**
     * 檢查是不是bot指令
     * @param string $message
     * @return boolean
     */
    public function checkIsNotBotCommand(string $message) {
        $check = true;

        $commands = [
            'start',
            'tt',
            'sl',
        ];
        
        foreach ($commands as $v) {
            if ($v == substr($message, 1, strlen($v))) {
                // 如果剛好是無參數的指令則略過
                if (in_array($message, ['/tt'])) {
                    $check = false;
                }
                break;
            }
        }
        
        return $check;
        
    }
}
