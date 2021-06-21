<?php
/**
 * 查市場相關統計資料的service
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\RemoteUrlService;
use App\Services\MarketDaysService;
use PHPHtmlParser\Dom;


class MarketDataService {

    /**
     * 取得並美化統計資料
     * @param string $type
     */
    public function getAndFormatMarketData(string $type = '') {
        $obj_remote_url = new RemoteUrlService();
        $obj_market_days = new MarketDaysService();
        if ($type == 'tse_market_close') {
            if ($obj_market_days->isTodayMarketOpen()) {
                // 收盤後取得加權指數等統計資料
                $url_tse_index = 'https://mis.twse.com.tw/stock/data/mis_ohlc_WWW.txt?_=' . time();
                $ret = $obj_remote_url->getUrl($url_tse_index, 600);
                $ret_decoded = json_decode($ret);
                
                // 加權指數
                $tse = $ret_decoded->infoArray[0]->z;
                // 前一天的加權指數
                $tse_last = $ret_decoded->infoArray[0]->y;
                // 成交量
                $volume = $ret_decoded->infoArray[0]->v;
                
                // 整理出字串
                $str_tse_index = $this->formatTseIndexInfo($tse, $tse_last, $volume);
                if ($str_tse_index == '') {
                    Log::error('加權指數資料有誤');
                    Log::error('URL: ' . $url_tse_index);
                return '';
                } else {
                    return $str_tse_index;
                }
            } else {
                Log::error('今天不是交易日');
                return '';
            }
            
        }
    }
    
    /**
     * 美化加權指數資料
     * @param unknown $tse      今天的加權指數
     * @param unknown $tse_last 昨天的加權指數
     * @param unknown $volume   成交量
     */
    private function formatTseIndexInfo($tse, $tse_last, $volume) {
        if (is_numeric($tse) &&
            is_numeric($tse_last) &&
            is_numeric($volume)) {
            // 算漲跌點數和百分比
            $difference = $tse - $tse_last;
            $percent = $difference / $tse_last * 100;
            $volume = $volume / 100;
            // 漲跌文字
            if ($difference > 0) {
                $updown = '△漲';
            } else {
                $updown = '▼跌';
            }
            
            // 文字模版
            $tse_index_format = '%d/%d/%d 加權指數 %.2f %s %.2f （%.2f％）成交量：%.2f 億';
            $tse_index_string = sprintf($tse_index_format, date('Y'), date('m'), date('d'), $tse, $updown, abs($difference), $percent, $volume);
            return $tse_index_string;
        } else {
            Log::error('無法正確處理加權指數資料');
            return '';
        }
    }

}
