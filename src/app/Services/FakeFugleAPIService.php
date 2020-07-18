<?php
/**
 * 假的富果API，開發測試時模擬用
 *
 */
namespace App\Services;

use Log;
use Exception;
use Storage;
use App\Services\MarketDaysService;
use DateTime;

class FakeFugleAPIService {

    
    /**
     * 讀富果API回傳存下來的檔案並回傳
     * @param string $goods
     * @return string
     */
    public function getRealtimeChartData(string $goods) {
        /*
         * 模擬盤中時段
         * 14:00~18:30
         * 19:00~23:30
         * 如果今天不是交易日，就用上一個交易日的資料
         */
        $obj_days = new MarketDaysService();

        // 今天是交易日嗎？
        if ($obj_days->isTodayMarketOpen()) {
            $fake_market_date = date('Ymd');
        } else {
            // 用上一個交易日的資料
            $fake_market_date = $obj_days->calculateMarketDays(date('Ymd'), '-', 1);
        }
        $fake_time = strtotime(substr($fake_market_date, 0, 4) . '-' . substr($fake_market_date, 4, 2) . '-' . substr($fake_market_date, 6, 2) . ' ' . date('H:i:s'));
        
        // 計算盤後模擬盤中的時間
        $shift_hours = 0;
        $dt_now = new DateTime();
        $dt_fake_open = new DateTime('14:00');
        $dt_fake_close = new DateTime('18:30');
        if (($dt_now >= $dt_fake_open) && ($dt_now <= $dt_fake_close)) {
            $shift_hours = 5;
        }
        
        if ($shift_hours == 0) {
            $dt_fake_open = new DateTime('19:00');
            $dt_fake_close = new DateTime('23:30');
            if (($dt_now >= $dt_fake_open) && ($dt_now <= $dt_fake_close)) {
                $shift_hours = 10;
            }
        }
        
        if ($shift_hours == 0) {
            $dt_fake_open = new DateTime('00:00');
            $dt_fake_close = new DateTime('04:30');
            if (($dt_now >= $dt_fake_open) && ($dt_now <= $dt_fake_close)) {
                $shift_hours = 15;
            }
        }
        
        $load_from = sprintf('fugle/%s/%s/%s.txt', date('Ymd', $fake_time), $goods, date('YmdHi', $fake_time - (60 * 60 * $shift_hours)));
        $data = Storage::get($load_from);
        return $data;
    }
}
