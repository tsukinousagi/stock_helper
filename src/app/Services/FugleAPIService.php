<?php
/**
 * 富果API存取
 *
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\RemoteUrlService;
use App\Services\StockGoodsService;
use App\Services\MarketDaysService;
use App\Repositories\PricesRepository;
use App\Enums\GoodsGraphDataType;
use PHPHtmlParser\Dom;


class FugleAPIService {

    
    /**
     * 打富果API取得即時價量資料
     * @param string $goods
     * @return string
     */
    public function getRealtimeChartData(string $goods) {
        // api 參數處理
        $api_url = 'https://api.fugle.tw/realtime/v0/intraday/chart?symbolId=%s&apiToken=%s';
        $api_url = sprintf($api_url, $goods, env('FUGLE_API_TOKEN', ''));
        
        // 打api
        $obj_remote = new RemoteUrlService();
        $obj_remote->setCooldownTime(1);
        $api_result = $obj_remote->getUrl($api_url, 1);
        
        return $api_result;
        
    }
}
