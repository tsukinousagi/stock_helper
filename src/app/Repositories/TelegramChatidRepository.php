<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\TelegramChatid;

class TelegramChatidRepository {
    /**
     * 新增或更新chatid對應的username
     * @param string $chatid
     * @param string $username
     * @return unknown
     */
    public function updateTelegramChatid(string $chatid, string $username) {
        $telegram_chatid = TelegramChatid::firstOrNew(
            [
                'chat_id' => $chatid,
            ]
        );
        $telegram_chatid->username = $username;
        return $telegram_chatid->save();
        
    }
    
}