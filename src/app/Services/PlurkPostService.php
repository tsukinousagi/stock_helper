<?php
/**
 * 商品價量相關資料處理
 * 
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\MarketDataService;


class PlurkPostService {
    
    /**
     * 發噗的進入點
     * @param string $type
     */
    public function makePlurk(string $type = '') {
        $obj_market_data = new MarketDataService();
        $ret = $obj_market_data->getAndFormatMarketData($type);
        // 不是空字串就發噗
        if ($ret <> '') {
            echo($ret . PHP_EOL);
            $this->postPlurk('shares', $ret);
        } else {
            echo('取得資料發生錯誤' . PHP_EOL);
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
