<?php
/**
 * 商品價量相關資料處理
 */
namespace App\Services;

use Log;
use Illuminate\Support\Facades\Redis;
use Exception;
use App\Services\GoodsPriceService;
use App\Services\GoodsHistoryPriceService;
use App\Services\StockGoodsService;


class DailyService {
    
    /**
     * 取得今天個股成交價量資訊
     * @return boolean
     */
    public function getTodayGoodsPrices() {
        /*
        $a = Redis::set('orz', 'sto');
        var_dump(Redis::get('orz'));
        */
        /*
//        $o = new Redis();
//        $o->connect(env('REDIS_HOST'));
        $o = Redis::connection();
        $o->set('orz', 'sto');
        $o->expire('orz', 1);
        var_dump($o->get('orz'));
        sleep(1);
        var_dump($o->get('orz'));
        return true;
        */
        /*
        $a = new RemoteUrlService();
        $url = 'http://usagi.tw/contact';
        for ($i = 1; $i <= 10; $i++) {
            echo($i . PHP_EOL);
            $r = $a->getUrl($url . '?' . rand(1,10000));
        }
        */

        // 先取得目前所有有效個股
        $obj_goods = new StockGoodsService();
        $result = $obj_goods->getAllValidGoods();
        
        $goods_each = 10; // 一次跑幾檔個股
//        tse_2330.tw_20200617|tse_2317.tw_20200617|tse_2454.tw_20200617|tse_3008.tw_20200617|tse_2412.tw_20200617|tse_1301.tw_20200617|tse_2891.tw_20200617|tse_1303.tw_20200617|tse_1216.tw_20200617|tse_2882.tw_20200617

        // 把個股分組
        $goods_groups = [];
        $single_group = [];
        foreach($result as $v) {
            $single_group[] = $v;
            if (sizeof($single_group) >= $goods_each) {
                $goods_group[] = $single_group;
                $single_group = [];
            }
        }
        $goods_group[] = $single_group; // 最後一組
        
        // 再分別取得價量資訊
//        $test_goods = ['2330', '2317', '1301', '2454', '3008'];
        $a = new GoodsPriceService();
        $today_str = date('Ymd');
        
        foreach($goods_group as $v) {
            $param = '';
            foreach($v as $v2) {
                if ($param <> '') {
                    $param .= ',';
                }
                $param .= $v2 . ',' . $today_str;
            }
            echo($param . PHP_EOL);
            $r = $a->getGoodsPrice($param);
//            $r = $a->getGoodsPrice($v . ',' . date('Ymd'));
            if ($r) {
                $s = $a->saveGoodPrices($r);
            } else {
                Log::error('取得個股價量資訊發生錯誤' . var_export($param));
            }
        }
    }

    /**
     * 由於有時候API取得的成交價量資訊會取不到，可以用這支去重新取得正確的價量數字
     * @return boolean
     */
    public function fixGoodsPrices() {
        // 取得資料不完整的個股資料
        $obj_price = new GoodsHistoryPriceService();
        $problem_goods = $obj_price->getProblemGoods();
        
        // 整理參數
        $problem_goods->each(function ($item, $key) use (&$codes, $obj_price) {
            // 打API取得正確價量資料並存入DB
            $codes = $item->code . ',' . substr($item->d_date, 0, 4) . substr($item->d_date, 5, 2) . substr($item->d_date, 8, 2);
            try {
                echo($codes . PHP_EOL);
                $price_data = $obj_price->getGoodsHistoryPrice($codes);
                $ret = $obj_price->saveGoodPrices($price_data);
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        });
        
        return true;
    }

}
