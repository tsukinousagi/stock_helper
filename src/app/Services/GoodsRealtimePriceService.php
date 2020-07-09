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
     * 取得個股即時價量資料
     * @return string
     */
    public function getGoodsRealtimeData() {
        $obj_price_minute = new PricesMinuteRepository();
        // 目前先為了程式開發方便，先直接打API並存成文字檔，做模擬用的API
        $goods = ['2417', '2498', '2520', '4979', '1785', '1402', '2504', '3711', '2317', '4142'];
        
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
        return true;
    }
}
