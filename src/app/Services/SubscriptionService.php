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
        $obj_subscription = new SubscriptionRepository();
        $obj_goods = new StockGoodsService();
        // 檢查訂閱數量
        $trace_turning_count = $obj_subscription->countTraceTurningByChatId($telegram_chat_id);
        $trace_turning_count_all = $obj_subscription->countAllTraceTurning($telegram_chat_id);
        $msg = 'blah';

        // 先看看這個股在不在
        if ($obj_goods->getGoodsType($goods)) {
            $goods_name = $obj_goods->getGoodsName($goods);
            $msg = $goods . ' ' . $goods_name . ':';
            // 先取得合理的結束日期
            $expire_at = $this->getExpireDateTime();
            // 先看看訂閱條件在不在
            $ret = $obj_subscription->getSubscription($telegram_chat_id, $goods, $goods_trace_type, $expire_at);
            if (sizeof($ret) <= 0) {
                if (($telegram_chat_id <> env('DEVELOPER_CHATID')) && ($trace_turning_count >= (int) env('TRACE_TURNING_LIMIT_BY_USER'))) {
                    $msg = "抱歉，你的商品追蹤數量已達上限，請刪除不需要的商品。\n";
                    $msg .= $this->getSubscriptionsByChatId($telegram_chat_id);
                } else if ($trace_turning_count_all >= (int) env('TRACE_TURNING_LIMIT')) {
                    $msg = "抱歉，全站的商品追蹤數量已達上限。\n";
                } else {
                    // 新增
                    $ret2 = $obj_subscription->updateSubscription($telegram_chat_id, $goods, $goods_trace_type, $expire_at);
                    $msg .= "股價轉折通知已設定\n";
                    // 判定收盤與否並顯示適當訊息
                    $obj_days = new MarketDaysService();
                    $today_open = $obj_days->isTodayMarketOpen();
                    $time_closed = $obj_days->isCurrentlyMarketClosed();
                    if (($today_open == true) && ($time_closed == false)) {
                        $msg .= '今天還沒收盤，設定股價轉折通知至今天收盤結束';
                    } else if (($today_open == true) && ($time_closed == true)) {
                        $msg .= '今天收盤了，於下一個交易日開盤後通知股價轉折，至收盤結束';
                    } else {
                        $msg .= '今天沒有開盤，於下一個交易日開盤後通知股價轉折，至收盤結束';
                    }
                    $msg .= "\n想取消通知，再輸入一次相同指令即可";
                }
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
        $obj_goods = new StockGoodsService();
        // 依訂閱類別作整理
        $ret = $obj_subscription->getSubscriptionByChatId($telegram_chat_id);
        $a_result = [];
        foreach($ret as $k => $v) {
            if (!isset($a_result[$v['goods_trace_type']])) {
                $a_result[$v['goods_trace_type']] = [];
            }
            $a_result[$v['goods_trace_type']][] = $v['goods'];
        }
        
        // 整理成文字輸出
        $msg = "以下是你目前的設定：\n\n";
        
        if (sizeof($a_result) > 0) {
            foreach($a_result as $k2 => $v2) {
                if ($k2 == GoodsTraceType::Turning) {
                    $msg .= "股價轉折通知：\n";
                    $goods = sort($v2);
                    foreach($v2 as $v3) {
                        $goods_name = $obj_goods->getGoodsName($v3);
                        $msg .= "- " . $v3 . " " . $goods_name . "\n";
                    }
                }
            }
        } else {
            $msg .= "無\n";
        }
        
        return $msg;
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
            if (time() > mktime(13, 35, 00)) {
                $set_on_next_market_day = true;
            }
        } else {
            // 今天不是交易日的話，就設定在下一個交易日的收盤時間
            $set_on_next_market_day = true;
        }
        
        // 設定到期日為今天收盤，或下一個交易日收盤
        if ($set_on_next_market_day) {
            $expire_at = $obj_days->calculateMarketDays(date('Ymd'), '+', 1);
            $expire_at = substr($expire_at, 0, 4) . '-' . substr($expire_at, 4, 2) . '-' . substr($expire_at, 6, 2) . ' 13:35:00';
        } else {
            $expire_at = date('Y-m-d') . ' 13:35:00';
        }
        
        return $expire_at;
        
    }
    
    /**
     * 取得目前仍在有效期間被訂閱的個股
     * @param GoodsTraceType $goods_trace_type
     * @return \App\Repositories\unknown
     */
    public function getAllActiveSubscriptions(GoodsTraceType $goods_trace_type) {
        $obj_subscription = new SubscriptionRepository();
        $ret = $obj_subscription->getAllActiveSubscriptions($goods_trace_type);

        $goods = [];
        foreach($ret as $v) {
            $goods[] = $v->goods;
        }
        return $goods;
    }
    
    /**
     * 取得目前仍在有效期間被訂閱的個股，並列出使用者
     * @param GoodsTraceType $goods_trace_type
     * @return \App\Repositories\unknown
     */
    public function getActiveSubscriptionsAndUsers(GoodsTraceType $goods_trace_type) {
        $obj_subscription = new SubscriptionRepository();
        $ret = $obj_subscription->getActiveSubscriptionsAndUsers($goods_trace_type);

        $goods = [];
        foreach($ret as $v) {
            if (!isset($goods[$v->goods])) {
                $goods[$v->goods] = [];
            }
            $goods[$v->goods][] = $v->telegram_chat_id;
        }
        return $goods;
    }
}