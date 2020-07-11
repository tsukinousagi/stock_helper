<?php
/**
 * 訂閱條件相關
 */
namespace App\Services;

use App\Services\MarketDaysService;
use App\Services\StockGoodsService;
use App\Repositories\SubscriptionRepository;
use App\Enums\GoodsTraceType;
use Log;

class SubscriptionService {
    
    /**
     * 切換是否訂閱
     * @param string $telegram_chat_id
     * @param string $goods
     * @param GoodsTraceType $goods_trace_type
     * @return string
     */
    public function toggleSubscription(string $telegram_chat_id, string $goods, GoodsTraceType $goods_trace_type) {
        // todo 做訂閱數量限制
        $obj_subscription = new SubscriptionRepository();
        $obj_goods = new StockGoodsService();
        $msg = '';
        // 先看看這個股在不在
        if ($obj_goods->getGoodsType($goods)) {
            $goods_name = $obj_goods->getGoodsName($goods);
            $msg = $goods . ' ' . $goods_name . ':';
            // 先取得合理的結束日期
            $expire_at = $this->getExpireDateTime();
            // 先看看訂閱條件在不在
            $ret = $obj_subscription->getSubscription($telegram_chat_id, $goods, $goods_trace_type);
            if (sizeof($ret) <= 0) {
                // 新增
                $ret2 = $obj_subscription->updateSubscription($telegram_chat_id, $goods, $goods_trace_type, $expire_at);
                $msg .= "股價轉折通知已設定\n";
                // 判定收盤與否並顯示適當訊息
                $obj_days = new MarketDaysService();
                $today_open = $obj_days->isTodayMarketOpen();
                $time_closed = $obj_days->isCurrentlyMarketClosed();
                if (($today_open == true) && ($time_close == false)) {
                    $msg .= '今天還沒收盤，設定股價轉折通知至今天收盤結束';
                } else if (($today_open == true) && ($time_close == true)) {
                    $msg .= '今天收盤了，於下一個交易日開盤後通知股價轉折，至收盤結束';
                } else {
                    $msg .= '今天沒有開盤，於下一個交易日開盤後通知股價轉折，至收盤結束';
                }
                $msg .= "\n想取消通知，再輸入一次相同指令即可";
            } else {
                // 刪除
                $ret2 = $obj_subscription->deleteSubscription($telegram_chat_id, $goods, $goods_trace_type, $expire_at);
                $msg .= "股價轉折通知已刪除\n想再次設定，再輸入一次相同指令即可";
            }
        } else {
            $msg = '指定的商品代號不存在！';
        }
        
        return $msg;
    }
    
    /**
     * 取得某chat_id目前所有的訂閱
     * @param string $telegram_chat_id
     * @return \App\Repositories\unknown
     */
    public function getSubscriptionsByChatId(string $telegram_chat_id) {
        $obj_subscription = new SubscriptionRepository();
        return $obj_subscription->getSubscriptionByChatId($telegram_chat_id);
    }
    
    
    /**
     * 計算合理的訂閱到期期限
     * @return string
     */
    private function getExpireDateTime() {
        $obj_days = new MarketDaysService();
        
        $set_on_next_market_day = false;
        if ($obj_days->isTodayMarketOpen() == true) {
            // 今天是交易日的話，看收盤了沒
            if (time() > mktime(14, 35, 00)) {
                $set_on_next_market_day = true;
            }
        } else {
            // 今天不是交易日的話，就設定在下一個交易日的收盤時間
            $set_on_next_market_day = true;
        }
        
        // 設定到期日為今天收盤，或下一個交易日收盤
        if ($set_on_next_market_day) {
            $expire_at = $obj_days->calculateMarketDays(date('Ymd'), '+', 1);
            $expire_at = substr($expire_at, 0, 4) . '-' . substr($expire_at, 4, 2) . '-' . substr($expire_at, 6, 2) . ' 14:35:00';
        } else {
            $expire_at = date('Y-m-d') . ' 14:35:00';
        }
        
        return $expire_at;
        
    }
}