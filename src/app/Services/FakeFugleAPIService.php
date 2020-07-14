<?php
/**
 * 假的富果API，開發測試時模擬用
 *
 */
namespace App\Services;

use Log;
use Exception;
use Storage;

class FakeFugleAPIService {

    
    /**
     * 讀富果API回傳存下來的檔案並回傳
     * @param string $goods
     * @return string
     */
    public function getRealtimeChartData(string $goods) {
        // todo 收盤後一律取當日最後一筆
        $load_from = sprintf('fugle/%s/%s/%s.txt', date('Ymd'), $goods, date('YmdHi', time() - (60 * 60 * 5)));
        $data = Storage::get($load_from);
        return $data;
    }
}
