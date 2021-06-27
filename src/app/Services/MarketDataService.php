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
            // php artisan command:plurkpost tse_market_close
            if ($obj_market_days->isTodayMarketOpen()) {
                // 收盤後取得加權指數等統計資料
                $url_tse_index = 'https://mis.twse.com.tw/stock/data/mis_ohlc_WWW.txt?_=' . time();
                $ret = $obj_remote_url->getUrl($url_tse_index, 300);
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
                $ret = $obj_remote_url->getUrl($url_juridical, 300);
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
            } else {
                Log::error('今天不是交易日');
                return '';
            }
        } else if ($type == 'us_index') {
            // php artisan command:plurkpost us_index
            if ($obj_market_days->isTodayMarketOpen()) {
                /**
                 * 四大指數資料
                 *        道瓊工業 那斯達克 標普500 費城半導體
                 * 指數
                 * 漲跌
                 * 百分比
                 */
                $index_data = [];
                $sub_dow = [];
                $sub_nasdaq = [];
                $sub_sp = [];
                $sub_phlx = [];

                // 美股四大指數資料
                $url_us_index = 'https://ws.api.cnyes.com/ws/api/v2/universal/quote?type=USINDEX&column=A&page=0&limit=50';
                $ret = $obj_remote_url->getUrl($url_us_index, 300);
                $ret_decoded = json_decode($ret, true);
                
                foreach($ret_decoded['data']['items'] as $v) {

                    if ($v['200009'] == '道瓊指數') {
                        $sub_dow = [$v['200009'], $v['6'], $v['11'], $v['56']];
                    } else if ($v['200009'] == 'NASDAQ') {
                        $sub_nasdaq = [$v['200009'], $v['6'], $v['11'], $v['56']];
                    } else if ($v['200009'] == 'S&P 500') {
                        $sub_sp = [$v['200009'], $v['6'], $v['11'], $v['56']];
                    } else if ($v['200009'] == '費城半導體') {
                        $sub_phlx = [$v['200009'], $v['6'], $v['11'], $v['56']];
                    }
                }
                
                $index_data = [$sub_dow, $sub_nasdaq, $sub_sp, $sub_phlx];

                // 整理出字串
                $str_us_index_exchange = $this->formatUSIndexInfo($index_data);
                if ($str_us_index_exchange == '') {
                    Log::error('美股四大指數資料有誤');
                    Log::error('URL: ' . $url_us_index);
                    return '';
                } else {
                    return $str_us_index_exchange;
                }
            } else {
                Log::error('今天不是交易日');
                return '';
            }
        } else if ($type == 'exchange_rate') {
            // php artisan command:plurkpost exchange_rate
            /**
             * 匯率資料
             */
            $data_exchange_rate = [
                'USD' => [],
                'CNY' => [],
                'HKD' => [],
                'JPY' => [],
                'EUR' => [],
            ];

            // 取得匯率資料
            $url_exchange_rate = 'https://tw.rter.info/capi.php';
            $ret = $obj_remote_url->getUrl($url_exchange_rate, 300);
            $ret_decoded = json_decode($ret, true);
            
            foreach($data_exchange_rate as $k => $v) {
                if ($k == 'USD') {
                    // 美元就直接取
                    $data_exchange_rate['USD'] = [
                        'caption' => '美元',
                        'code' => 'USD',
                        'rate' => sprintf('%.2f', $ret_decoded['USDTWD']['Exrate']),
                    ];
                } else if ($k == 'CNY') {
                    // 人民幣
                    $data_exchange_rate['CNY'] = [
                        'caption' => '人民幣',
                        'code' => 'CNY',
                        'rate' => sprintf('%.3f', ($ret_decoded['USDTWD']['Exrate'] / $ret_decoded['USDCNY']['Exrate'])),
                    ];
                } else if ($k == 'HKD') {
                    // 港幣
                    $data_exchange_rate['HKD'] = [
                        'caption' => '港幣',
                        'code' => 'HKD',
                        'rate' => sprintf('%.3f', ($ret_decoded['USDTWD']['Exrate'] / $ret_decoded['USDHKD']['Exrate'])),
                    ];
                } else if ($k == 'JPY') {
                    // 日圓
                    $data_exchange_rate['JPY'] = [
                        'caption' => '日圓',
                        'code' => 'JPY',
                        'rate' => sprintf('%.4f', ($ret_decoded['USDTWD']['Exrate'] / $ret_decoded['USDJPY']['Exrate'])),
                    ];
                } else if ($k == 'EUR') {
                    // 歐元
                    $data_exchange_rate['EUR'] = [
                        'caption' => '歐元',
                        'code' => 'EUR',
                        'rate' => sprintf('%.2f', ($ret_decoded['USDTWD']['Exrate'] / $ret_decoded['USDEUR']['Exrate'])),
                    ];
                }
            }
            
            // 整理出字串
            $str_exchange_rate = $this->formatExchangeRateInfo($data_exchange_rate);
            if ($str_exchange_rate == '') {
                Log::error('匯率資料有誤');
                Log::error('URL: ' . $url_exchange_rate);
                return '';
            } else {
                return $str_exchange_rate;
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
            $updown = $this->UpDownIconText($difference);
            
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
     * 美化美股四大指數資料
     * @param unknown $index_data
     */
    private function formatUSIndexInfo($index_data) {
        $line_break = PHP_EOL;
        $us_index_string = sprintf('%d/%d/%d 美股四大指數', date('Y'), date('m'), date('d'));
        foreach ($index_data as $v) {
            $us_index_string .= $line_break;
            $row = sprintf('%s %.2f %s %.2f （%.2f%%）',
                $v[0],
                $v[1],
                $this->UpDownIconText($v[2]),
                $v[2],
                $v[3]);
            $us_index_string .= $row;
        }
        return $us_index_string;
        
    }
    
    /**
     * 美化匯率資料
     * @param unknown $exchange_rate
     * @return string
     */
    private function formatExchangeRateInfo($exchange_rate) {
        $line_break = PHP_EOL;
        $exchange_rate_string = sprintf('%d/%d/%d 全球主要貨幣匯率', date('Y'), date('m'), date('d'));
        foreach ($exchange_rate as $v) {
            $exchange_rate_string .= $line_break;
            $row = sprintf('%s(%s) %s', $v['caption'], $v['code'] ,$v['rate']);
            $exchange_rate_string .= $row;
        }
        $exchange_rate_string .= $line_break;
        $exchange_rate_string .= '註：以上為該幣別兌新台幣的數值';
        return $exchange_rate_string;
        
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
    
    /**
     * 漲跌文字
     * @param unknown $amount
     */
    private function UpDownIconText($amount) {
        if ($amount > 0) {
            return '△';
        } else {
            return '▼';
        }
    }

}
