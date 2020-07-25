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
    public function updateTelegramChatid(string $chatid, string $username, string $firstname, string $lastname) {
        $telegram_chatid = TelegramChatid::firstOrNew(
            [
                'chat_id' => $chatid,
            ]
        );
        $telegram_chatid->username = $username;
        $telegram_chatid->firstname = $firstname;
        $telegram_chatid->lastname = $lastname;
        return $telegram_chatid->save();
        
    }
    
}