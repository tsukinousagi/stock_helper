<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Subscriptions;
use App\Enums\GoodsTraceType;

class SubscriptionRepository {

    /**
     * 新增或更新訂閱
     * @param string $telegram_chat_id
     * @param string $goods
     * @param GoodsTraceType $goods_trace_type
     * @param string $expire_at
     * @return unknown
     */
    public function updateSubscription(string $telegram_chat_id, string $goods, GoodsTraceType $goods_trace_type, string $expire_at) {
        $subscriptions = Subscriptions::firstOrNew(
            [
                'telegram_chat_id' => $telegram_chat_id,
                'goods' => $goods,
                'goods_trace_type' => $goods_trace_type,
                
            ]
        );
        $subscriptions->expire_at = $expire_at;
        return $subscriptions->save();
        
    }
    
    /**
     * 確認某訂閱條件是否存在
     * @param string $telegram_chat_id
     * @param string $goods
     * @param GoodsTraceType $goods_trace_type
     * @return unknown
     */
    public function getSubscription(string $telegram_chat_id, string $goods, GoodsTraceType $goods_trace_type, string $market_closed_time) {
        $subscriptions = Subscriptions::where('telegram_chat_id', $telegram_chat_id)
        ->where('goods', $goods)
        ->where('goods_trace_type', $goods_trace_type->value)
        ->where('expire_at', '>=', $market_closed_time)
        ->get();
        return $subscriptions;
    }
    

    /**
     * 刪除某訂閱條件
     * @param string $telegram_chat_id
     * @param string $goods
     * @param GoodsTraceType $goods_trace_type
     * @return unknown
     */
    public function deleteSubscription(string $telegram_chat_id, string $goods, GoodsTraceType $goods_trace_type) {
        $subscriptions = Subscriptions::where('telegram_chat_id', $telegram_chat_id)
        ->where('goods', $goods)
        ->where('goods_trace_type', $goods_trace_type->value)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->delete();
        return $subscriptions;
    }
    
    
    /**
     * 取得某人目前的訂閱條件
     * @param string $telegram_chat_id
     * @return unknown
     */
    public function getSubscriptionByChatId(string $telegram_chat_id) {
        $subscriptions = Subscriptions::where('telegram_chat_id', $telegram_chat_id)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->get();
        return $subscriptions;
    }

    /**
     * 取得某人目前訂閱了幾檔個股
     * @param string $telegram_chat_id
     * @return unknown
     */
    public function countTraceTurningByChatId(string $telegram_chat_id) {
        $subscriptions = Subscriptions::where('telegram_chat_id', $telegram_chat_id)
        ->where('goods_trace_type', '=', GoodsTraceType::Turning)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->count();
        return $subscriptions;
    }
    

    /**
     * 取得所有人目前訂閱了幾檔個股
     * @return unknown
     */
    public function countAllTraceTurning() {
        $subscriptions = Subscriptions::where('goods_trace_type', '=', GoodsTraceType::Turning)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->count();
        return $subscriptions;
    }
    
    /**
     * 取得目前仍在有效期間被訂閱的個股
     * @param GoodsTraceType $goods_trace_type
     * @return unknown
     */
    public function getAllActiveSubscriptions(GoodsTraceType $goods_trace_type) {
        $subscriptions = Subscriptions::where('goods_trace_type', $goods_trace_type->value)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->select('goods')
        ->distinct('goods')
        ->get();
        return $subscriptions;
    }
    
    /**
     * 取得目前仍在有效期間被訂閱的個股，並列出使用者
     * @param GoodsTraceType $goods_trace_type
     * @return unknown
     */
    public function getActiveSubscriptionsAndUsers(GoodsTraceType $goods_trace_type) {
        $subscriptions = Subscriptions::where('goods_trace_type', $goods_trace_type->value)
        ->where('expire_at', '>', date('Y-m-d H:i:s'))
        ->select('goods', 'telegram_chat_id')
        ->orderBy('goods', 'asc')
        ->orderBy('telegram_chat_id', 'asc')
        ->get();
        return $subscriptions;
    }
    // todo 清除所有過期的訂閱
}