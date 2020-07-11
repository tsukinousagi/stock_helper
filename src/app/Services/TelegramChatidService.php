<?php
/**
 * 處理Telegram Bot的API
 */
namespace App\Services;

use Log;
use Exception;
use Telegram;
use App\Repositories\TelegramChatidRepository;


class TelegramChatidService {

    
    public function saveTelegramChatidInfo($update) {
        // 取得要記錄的資訊
        $chat_id = $update->getMessage()->getChat()->getId();
        $chat_username = $update->getMessage()->getChat()->getUsername();
        
        // 寫入資料庫
        $obj_chatid = new TelegramChatidRepository();
        $ret = $obj_chatid->updateTelegramChatid($chat_id, $chat_username);

        return $chat_id;
    }
}
