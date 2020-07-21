<?php
/**
 * 個股即時價量資料
 *
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\StockGoodsService;
use App\Services\MarketDaysService;
use App\Enums\GoodsGraphDataType;
use App\Services\FugleAPIService;
use App\Repositories\PricesMinuteRepository;
use Illuminate\Support\Facades\Storage;
use App\Enums\GoodsTraceType;


class GoodsRealtimePriceService {
    
    /**
     * 取得個股即時價量資料
     * @param string $goods
     * @return string
     */
    public function getGoodsRealtimeDataByGoods(string $goods) {
        $obj_fugle = new FugleAPIService();
        $ret = $obj_fugle->getRealtimeChartData($goods);
        
        return $ret;
    }
    
    /**
     * 假的富果API
     * @param string $goods
     * @return string
     */
    public function fakeAPIFugleGraph(string $goods) {
        $obj_fugle = new FugleAPIService();
        $ret = $obj_fugle->fakeAPIFugleGraph($goods);
        
        return $ret;
    }
    
    /**
     * 取得個股即時價量資料
     * @return string
     */
    public function getGoodsRealtimeData() {
        // 測量執行時間
        $ts_begin = time();

        $obj_price_minute = new PricesMinuteRepository();
        $obj_subscription = new SubscriptionService();
        $obj_turning = new GoodsPriceTurningService();
        $obj_telegram = new TelegramBotAPIService();
        $obj_goods = new StockGoodsService();
        
        // 切換是否用假的API
        $fake_api = false;
        if ($fake_api) {
            echo('使用假API' . PHP_EOL);
        } else {
            echo('使用真API' . PHP_EOL);
        }

        // 先取得目前在監控期間的有哪些個股
        // todo 限制數量避免把API打爆
        // $goods = $obj_subscription->getAllActiveSubscriptions(GoodsTraceType::Turning());

        // 目前先為了程式開發方便，先直接打API並存成文字檔，做模擬用的API
        $goods = [
            '2417', '2498', '2520', '4979', '1785', '1402', '2504', '3711', '2317', '4142',
            '1210', '8926', '1907', '1734', '2356', '2547', '3234', '3231', '2347', '2301',
        ];

        $goods_direction = [];
        foreach($goods as $v) {
            // 打API取得每分鐘價量資料（要存DB嗎？）
            if ($fake_api) {
                $ret = $this->fakeAPIFugleGraph($v);
            } else {
                $ret = $this->getGoodsRealtimeDataByGoods($v);
            }

            if ($ret) {
                $graph_data = json_decode($ret, true);
                // 將最新一筆寫入資料庫
                $part = array_slice($graph_data['data']['chart'], -2);
                $ret3 = $this->goodsRealtimeDataToDB($v, $part);
            } else {
                Log::error(sprintf('取得%s的即時價量時發生錯誤', $v));
                continue;
            }
            
            if ($graph_data) {
                // 用API的結果找出轉折
                $direction = $obj_turning->findPriceDirection($graph_data['data']['chart']);
                echo($v . $obj_turning->getDirectionText($direction) . PHP_EOL);
                // 寫入暫存用陣列
                $goods_direction[] = [
                    'goods' => $v,
                    'direction' => $direction,
                ];
            } else {
                Log::error(sprintf('解析%s的即時價量時發生錯誤', $v));
                continue;
            }
            
            // 如果是真的API，寫一份文字檔在storage
            if (!$fake_api) {
                // 存檔路徑
                $save_to = sprintf('fugle/%s/%s/%s.txt', date('Ymd'), $v, date('YmdHi'));
                
                // 存檔
                $ret4 = Storage::put($save_to, $ret);
            }
            
        }
        
        $goods_direction_changed = [];
        // 逐一檢查個股是否已出現轉折
        foreach($goods_direction as $v3) {
            $changed = $obj_turning->checkIfDirectionChanged($v3['goods'], $v3['direction']);
            if ($changed) {
                $goods_direction_changed[] = $v3;
            }
        }
        
        // 寫入轉折記錄
        $telegram_message = '';
        foreach($goods_direction_changed as $v4) {

            if ($telegram_message <> '') {
                $telegram_message .= PHP_EOL;
            }

            $telegram_message .= sprintf('%s：%s', 
                $v4['goods'] . $obj_goods->getGoodsName($v4['goods']), 
                $obj_turning->getDirectionText($v4['direction'])
            );
        
            echo($telegram_message . PHP_EOL);
            $ret = $obj_turning->savePriceDirectionChange($v4['goods'], $v4['direction']);
        }

        if ($telegram_message <> '') {
            // 發通知
            $ret5 = $obj_telegram->sendMessageViaTelegramBot(env('DEVELOPER_CHATID'), $telegram_message);
        }
        
        /* 
         * 想一下這邊邏輯要怎麼寫....
         * 
         * 先取得目前在監控期間的有哪些個股
         * 
         * 然後打API取得每分鐘價量資料（要存DB嗎？）
         * 
         * 往回推每一筆，與最近的做比對
         * 
         * 如果有漲＆跌超過0.5(再想)，去比對最近與最近通知過的漲跌方向是否相反
         * 
         * 如果相反了代表發生轉折，通知當初有設定監控此個股的使用者
         * 
         * 並且寫入通知記錄
         */
        // 目前先為了程式開發方便，先直接打API並存成文字檔，做模擬用的API
        /*
        $goods = [
            '2417', '2498', '2520', '4979', '1785', '1402', '2504', '3711', '2317', '4142',
            '1210', '8926', '1907', '1734', '2356', '2547', '3234', '3231', '2347', '2301',
        ];
        
        foreach($goods as $v) {
            // 存檔路徑
            $save_to = sprintf('fugle/%s/%s/%s.txt', date('Ymd'), $v, date('YmdHi'));

            // 打api
            $ret = $this->getGoodsRealtimeDataByGoods($v);
            
            // 寫入資料庫
            $data = json_decode($ret, true);
            $part = array_slice($data['data']['chart'], -2);
            foreach($part as $k2 => $v2) {
                // 取本地時間
                $dt_local = date('Y-m-d H:i:s', strtotime($k2));
                
                // 準備塞db的資料
                $row = [
                    'code' => $v,
                    'd_date' => $dt_local,
                    'price_open' => $v2['open'],
                    'price_high' => $v2['high'],
                    'price_low' => $v2['low'],
                    'price_close' => $v2['close'],
                    'volume' => ($v2['volume'] / 1000),
                ];
                
                $obj_price_minute->updatePrices($row);
            }
            

            // 存檔
            $ret2 = Storage::put($save_to, $ret);
        }
        */
        // 測量執行時間
        $ts_end = time();
        echo(sprintf('執行時間%.2f秒', ($ts_end - $ts_begin)));
        return true;
    }
    
    /**
     * 即時價量寫入資料庫
     */
    private function goodsRealtimeDataToDB(string $goods, array $price_data) {
        $obj_price_minute = new PricesMinuteRepository();
        
        try {

            foreach($price_data as $k => $v) {
                // 取本地時間
                $dt_local = date('Y-m-d H:i:s', strtotime($k));
                
                // 準備塞db的資料
                $row = [
                    'code' => $goods,
                    'd_date' => $dt_local,
                    'price_open' => $v['open'],
                    'price_high' => $v['high'],
                    'price_low' => $v['low'],
                    'price_close' => $v['close'],
                    'volume' => ($v['volume'] / 1000),
                ];
                
                $ret = $obj_price_minute->updatePrices($row);
            }
        } catch (Exception $e) {
            Log::error('寫入即時價量資料發生錯誤');
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            Log::error($res->getStatusCode());
            return false;

        }
        
        return true;
    }
}
