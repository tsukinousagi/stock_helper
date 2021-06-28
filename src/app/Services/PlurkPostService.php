<?php
/**
 * 商品價量相關資料處理
 * 
 */
namespace App\Services;

use Log;
use Exception;
use Illuminate\Support\Facades\Cache;
use App\Services\MarketDataService;


class PlurkPostService {
    
    /**
     * 發噗的進入點
     * @param string $type
     */
    public function makePlurk(string $type = '') {
        $check_key = date('Ymd') . $type;
        if (Cache::get($check_key) == false) { // 偵測是否發過相同的噗
            $obj_market_data = new MarketDataService();
            $ret = $obj_market_data->getAndFormatMarketData($type);
            // 不是空字串就發噗
            // todo 研究如何回覆自己的噗，之後考慮附加一些資訊
            if ((isset($ret[0])) && ($ret[0] <> '')) {
                echo($ret[0] . PHP_EOL);
                $this->postPlurk('shares', $ret);
                // 寫入檢查用cache
                Cache::put($check_key, rand(0, 9999), 1800);
            } else {
                echo('取得資料發生錯誤' . PHP_EOL);
            }
        } else {
            echo('相同的噗之前發過了' . PHP_EOL);
        }
    }

    
    public function postPlurk(string $qualifier = '', string $content = '') {
        $qlurk = new \Qlurk\ApiClient(env('PLURK_APP_KEY'), env('PLURK_APP_SECRET'), env('PLURK_TOKEN'), env('PLURK_SECRET'));
//        $resp = $qlurk->call('/APP/Polling/getPlurks', ['offset' => 0, 'limit' => 20]);
        $resp = $qlurk->call('/APP/Timeline/plurkAdd', [
            'content' => $content,
            'qualifier' => $qualifier,
            'lang' => 'tr_ch',
            
        ]);
        if (isset($resp['error_text'])) {
            return $resp['error_text'];
        } else {
            return 'OK';
        }
    }
}
