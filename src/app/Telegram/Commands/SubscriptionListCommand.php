<?php
/**
 * 列出個股訂閱情況
 */

namespace App\Telegram\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Services\SubscriptionService;
use App\Services\TelegramChatidService;
use App\Enums\GoodsTraceType;

class SubscriptionListCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "sl";
    
    /**
     * @var string Command Description
     */
    protected $description = "列出目前有使用本bot觀察的商品";
    
    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        // 記錄chatid
        $obj_chatid = new TelegramChatidService();
        $chatid = $obj_chatid->saveTelegramChatidInfo($this->getUpdate());

        $obj_subscription = new SubscriptionService();
        $msg = $obj_subscription->getSubscriptionsByChatId($chatid);
        $this->replyWithMessage(
            [
                'text' => $msg,
            ]
        );
        
    }
}