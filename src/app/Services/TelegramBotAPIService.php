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
        
        foreach($response as $v) {
            $this->replyIfNotCommand($v);
        }

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

        $check = true;
        $message = $response['message']['text'];
        
        Log::info($message);
        
        // 檢查是不是用斜線開頭
        if (substr($message, 0, 1) == '/') {
            // 檢查是不是有效指令
            if ($check) {
                $check = $this->checkIsNotBotCommand($message);
            }
        }


        $ret = true;
        // 發訊息回應
        if ($check) {
            $chatid = $response['message']['chat']['id'];
            $ret = $this->sendMessageViaTelegramBot($chatid, '輸入錯誤，請使用 /start 指令觀看說明');
        }
        return $ret;
    }
    
    /**
     * 檢查是不是bot指令
     * @param string $message
     * @return boolean
     */
    public function checkIsNotBotCommand(string $message) {
        $check = true;

        $commands_no_param = [
            'start',
            'tt',
            'sl',
        ];
        
        foreach ($commands_no_param as $v) {
            if ($v == substr($message, 1)) {
                $check = false;
                break;
            }
        }
        
        $commands_param = [
            'tt ',
        ];
        
        foreach ($commands_param as $v) {
            if ($v == substr($message, 1, strlen($v))) {
                $check = false;
                break;
            }
        }
        
        return $check;
        
    }
}
