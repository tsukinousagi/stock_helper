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
        $chat_username = $update->getMessage()->getChat()->getUsername() ?? '';
        $chat_firstname = $update->getMessage()->getChat()->getFirstName() ?? '';
        $chat_lastname = $update->getMessage()->getChat()->getLastName() ?? '';
        
        // 寫入資料庫
        $obj_chatid = new TelegramChatidRepository();
        $ret = $obj_chatid->updateTelegramChatid($chat_id, $chat_username, $chat_firstname, $chat_lastname);

        return $chat_id;
    }
}
