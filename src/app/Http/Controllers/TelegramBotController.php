<?php
/**
 * Telegram Webhook相關
 * 
 * todo 寫個可以透過web開關webhook的功能
 * todo 寫個直接輸入個股就能操作相關功能的機制
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Log;
use App\Services\TelegramBotAPIService;

class TelegramBotController extends Controller
{
    /**
     * 給Telegram官方接的
     * @param Request $request
     * @return string
     */
    public function webhook(Request $request) {
        Log::info(__CLASS__ . ':' . __FUNCTION__);
//        $updates = Telegram::getWebhookUpdates();
//        Log::info($updates);
        $response = Telegram::commandsHandler(true);
        Log::info($response);
        return 'ok';
    }
    
    
    /**
     * 讓自己可以用http request開關webhook
     * @param Request $request
     * @return string
     */
    public function webhookToggle(Request $request) {
        $toggle = $request->toggle;
        $obj_telegram = new TelegramBotAPIService();
        
        // 檢查參數
        if (in_array($toggle, ['on', 'enable'])) {
            $action_text = '開啟';
            $ret = $obj_telegram->setWebhook();
        } else if (in_array($toggle, ['off', 'disable'])) {
            $action_text = '關閉';
            $ret = $obj_telegram->deleteWebhook();
        }
        
        // 回傳操作結果
        if ($ret) {
            $msg = $action_text . '成功';
        } else {
            $msg = $action_text . '失敗';
        }
        
        return $msg;
    }
    
}
