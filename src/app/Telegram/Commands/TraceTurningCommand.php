<?php
/**
 * 追蹤股價轉折
 */

namespace App\Telegram\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Services\SubscriptionService;
use App\Services\TelegramChatidService;
use App\Enums\GoodsTraceType;

class TraceTurningCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "tt";
    
    /**
     * @var string Command Description
     */
    protected $description = "追蹤股價轉折";
    
    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        // 記錄chatid
        $obj_chatid = new TelegramChatidService();
        $chatid = $obj_chatid->saveTelegramChatidInfo($this->getUpdate());

        // 設定或取消訂閱
        if ($arguments == '') {
            $this->replyWithMessage(
                [
                    'text' => "請直接在參數後加上要追蹤的商品代碼，就可以設定股價轉折通知。\n範例： /tt 2884",
                ]
            );
        } else {
            $obj_subscription = new SubscriptionService();
            $msg = $obj_subscription->toggleSubscription($chatid, $arguments, GoodsTraceType::Turning());
            $this->replyWithMessage(
                [
                    'text' => $msg,
                ]
            );

        }
        
        
        
    }
}