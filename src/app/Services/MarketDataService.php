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
        // todo
        // 美股四大指數
        // 外幣匯率
        if ($type == 'tse_market_close') {
            // php artisan command:plurkpost tse_market_close
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
        } else if ($type == 'juridical_exchange') {
            // php artisan command:plurkpost juridical_exchange
            if ($obj_market_days->isTodayMarketOpen()) {
                // 外資、投信、自營
                $amount_foreign = 0;
                $amount_fund = 0;
                $amount_self = 0;

                // 收盤後取得三大法人買賣超統計
                $url_juridical = 'https://www.twse.com.tw/fund/BFI82U?response=json&dayDate=&weekDate=&monthDate=&type=day&_=' . time();
                $ret = $obj_remote_url->getUrl($url_juridical, 3600);
                $ret_decoded = json_decode($ret);

                $amount_foreign = $this->removeNumberComma($ret_decoded->data[3][3]);
                $amount_fund = $this->removeNumberComma($ret_decoded->data[2][3]);
                $amount_self = $this->removeNumberComma($ret_decoded->data[0][3]) + $this->removeNumberComma($ret_decoded->data[1][3]);

                // 整理出字串
                $str_juridical_exchange = $this->formatJuridicalExchangeInfo($amount_foreign, $amount_fund, $amount_self);
                if ($str_juridical_exchange == '') {
                    Log::error('三大法人買賣超資料有誤');
                    Log::error('URL: ' . $url_juridical);
                return '';
                } else {
                    return $str_juridical_exchange;
                }
                /*
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
                */
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
            $tse_index_format = '%d/%d/%d 加權指數 %.2f %s %.2f （%.2f%%）成交量：%.2f 億';
            $tse_index_string = sprintf($tse_index_format, date('Y'), date('m'), date('d'), $tse, $updown, abs($difference), $percent, $volume);
            return $tse_index_string;
        } else {
            Log::error('無法正確處理加權指數資料');
            return '';
        }
    }
    
    /**
     * 美化三大法人買賣超資料
     * @param unknown $foreign
     * @param unknown $fund
     * @param unknown $self
     * @return string
     */
    private function formatJuridicalExchangeInfo($foreign, $fund, $self) {
        if (is_numeric($foreign) &&
            is_numeric($fund) &&
            is_numeric($self)) {
            
            // 四捨五入到億元
            $foreign_billion = round($foreign / 100000000);
            $fund_billion = round($fund / 100000000);
            $self_billion = round($self / 100000000);
            
            // 文字模版
            $juridical_exchange_format = '%d/%d/%d 三大法人動向 外資%s %d 億 投信%s %d 億 自營商%s %d 億 ';
            $juridical_exchange_string = sprintf(
                $juridical_exchange_format, 
                date('Y'), 
                date('m'), 
                date('d'), 
                $this->buySellText($foreign),
                abs($foreign_billion),
                $this->buySellText($fund),
                abs($fund_billion),
                $this->buySellText($self),
                abs($self_billion)
                );
            return $juridical_exchange_string;
        } else {
            Log::error('無法正確處理三大法人買賣超資料');
            return '';
        }
    }
    
    /**
     * 去除數字逗號
     * @param string $num
     */
    private function removeNumberComma(string $num) {
        return floatval(str_replace(',', '', $num));
    }
    
    /**
     * 傳回買賣超文字
     * @param unknown $amount
     */
    private function buySellText($amount) {
        if ($amount > 0) {
            return '買超';
        } else {
            return '賣超';
        }
    }

}
