<?php
/**
 * 商品價量相關資料處理
 *
 * 另一支API https://www.twse.com.tw/exchangeReport/STOCK_DAY?response=json&date=20200617&stockNo=1213&_=1592384560673
 * https://www.tpex.org.tw/web/stock/aftertrading/daily_trading_info/st43_result.php?l=zh-tw&d=109/06&stkno=8044&_=1592405228637
 * 查有問題的資料 SELECT * FROM `prices` WHERE (price_open = (-1) or price_high  = (-1) or price_low  = (-1) or  price_close  = (-1) or volume  = (-1)) and d_date = '2020-06-17 14:30:00'
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


class GoodsPriceService {

    /**
     * 取得個股價量資訊
     * @param string $codes 代碼,日期,代碼,日期,代碼,日期,代碼,日期,....
     */
    public function getGoodsPrice(string $codes) {
        $obj_goods = new StockGoodsService();

        // 預先處理參數
        $arr_codes = explode(',', $codes);
        $ok_codes = [];
        for($i = 0; $i < sizeof($arr_codes); $i = $i + 2) {

            // 略過不完整的參數
            if (isset($arr_codes[$i]) && isset($arr_codes[$i + 1])) {

                // 先看這筆是上市 上櫃 ETF
                $goods_type = $obj_goods->getGoodsType($arr_codes[$i]);
                if ($goods_type == '上市') {
                    $goods_type_param = 'tse';
                } else if ($goods_type == '上櫃') {
                    $goods_type_param = 'otc';
                } else if ($goods_type == 'ETF') {
                    $goods_type_param = 'tse';
                } else {
                    $goods_type_param = '';
                }

                if ($goods_type_param <> '' ) {
                    $ok_code = [
                        'code' => $arr_codes[$i],
                        'd_date' => $arr_codes[$i + 1],
                        'goods_type' => $goods_type,
                        'goods_type_param' => $goods_type_param,
                    ];
                    $ok_codes[] = $ok_code;
                }
            }

        }

        // 處理網址商品參數部份
        $url_goods_part = '';
        foreach($ok_codes as $v) {
            if ($url_goods_part <> '') {
                $url_goods_part .= '|';
            }
            $url_single_code_part = sprintf('%s_%s.tw_%s', $v['goods_type_param'], $v['code'], $v['d_date']);
            $url_goods_part .= $url_single_code_part;
        }

        // 網址，參數分別為代號，日期和timestamp
        //$url = 'https://mis.twse.com.tw/stock/api/getStockInfo.jsp?ex_ch=%s&_=%s';
        $url = 'https://mis.twse.com.tw/stock/api/getStockInfo.jsp?ex_ch=%s';
        // note: api的fv是盤後，ov是零股，大部份的看盤網站和軟體都會再加這兩個數值作當日總成交量

        // 打api取得資料
        $a = new RemoteUrlService();
        //$url = sprintf($url, $url_goods_part, time());
        $url = sprintf($url, $url_goods_part);
        $r = $a->getUrl($url, 21600);
        $r_json = json_decode($r);
        if (isset($r_json->rtcode) && ($r_json->rtcode == '0000')) {
            $price_data = [];
            foreach($r_json->msgArray as $v) {
                $format = [];
                // 代號
                $format['code'] = $v->c ?? '';
                // 名稱
                $format['name'] = $v->n ?? '';
                // 日期
                $format['date_code'] = $v->d ?? '';
                // 開盤價
                $format['price_open'] = $v->o ?? '';
                // 最高價
                $format['price_high'] = $v->h ?? '';
                // 最低價
                $format['price_low'] = $v->l ?? '';
                // 收盤價
                $format['price_close'] = $v->z ?? ''; // 盤中打API會取得目前股價，昨日收盤價是另一個欄位
                // 成交量
                // $format['volume'] = ((int)$v->v + (int)$v->fv + ((int)$v->ov / 1000)); // 盤中打API會取得目前累計成交量
                $format['volume'] = $v->v ?? ''; // 盤中打API會取得目前累計成交量
                if ($format['volume'] <> '') {
                    // 成交量(盤後)
                    if (isset($v->fv)) {
                        $format['volume'] += (int)$v->fv;
                    }
                    // 成交量(零股)
                    if (isset($v->ov)) {
                        $format['volume'] += ((int)$v->ov / 1000);
                    }
                }

                $price_data[] = $format;
            }
            return $price_data;
        } else {
            return false;
        }
    }

    /**
     * 處理api拉下來的資料
     * @param array $price_data
     * @return boolean
     */
    public function saveGoodPrices(array $prices_data) {
        foreach($prices_data as $v) {
            $ret = $this->goodPricesToDB($v);
        }
        return $ret;
    }

    /**
     * 將個股某日的價量資訊寫入db
     * @param array $price_data
     * @return boolean
     */
    public function goodPricesToDB(array $price_data) {
        try {
            // 檢查參數
            if ((!isset($price_data['code'])) ||
                (!isset($price_data['date_code'])) ||
                (!isset($price_data['price_open'])) ||
                (!isset($price_data['price_high'])) ||
                (!isset($price_data['price_low'])) ||
                (!isset($price_data['price_close'])) ||
                (!isset($price_data['volume']))) {
                throw new Exception('欄位不可為空' . var_export($price_data));
            }

            // 檢查價量
            if (floatval($price_data['price_open']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_open'], $price_data)) {
                    $price_data['price_open'] = (-1);
                } else {
                    throw new Exception('開盤價錯誤: ' . $price_data['price_open']);
                }
            }
            if (floatval($price_data['price_high']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_high'], $price_data)) {
                    $price_data['price_high'] = (-1);
                } else {
                    throw new Exception('最高價錯誤: ' . $price_data['price_high']);
                }
            }
            if (floatval($price_data['price_low']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_low'], $price_data)) {
                    $price_data['price_low'] = (-1);
                } else {
                    throw new Exception('最低價錯誤: ' . $price_data['price_low']);
                }
            }
            if (floatval($price_data['price_close']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_close'], $price_data)) {
                    $price_data['price_close'] = (-1);
                } else {
                    throw new Exception('收盤價錯誤: ' . $price_data['price_close']);
                }
            }
            if (floatval($price_data['volume']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['volume'], $price_data)) {
                    $price_data['volume'] = (-1);
                } else {
                    throw new Exception('成交量錯誤: ' . $price_data['volume']);
                }
            }

            // 檢查交易日
            $p_year = (int)substr($price_data['date_code'], 0, 4);
            $p_month = (int)substr($price_data['date_code'], 4, 2);
            $p_day = (int)substr($price_data['date_code'], 6, 2);
            if (!checkdate($p_month, $p_day, $p_year)) {
                throw new Exception('交易日格式錯誤');
            }

            $date_str = sprintf('%04d-%02d-%02d 14:30:00', $p_year, $p_month, $p_day);
            if (strtotime($date_str) > time()) {
                throw new Exception('交易日超過今天了');
            }

            // 寫入db
            $obj_price = new PricesRepository();
            $data = [
                'code' => $price_data['code'],
                'd_date' => $date_str,
                'price_open' => $price_data['price_open'],
                'price_high' => $price_data['price_high'],
                'price_low' => $price_data['price_low'],
                'price_close' => $price_data['price_close'],
                'volume' => $price_data['volume'],
            ];
            return $obj_price->updatePrices($data);
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
        }
    }

    /**
     * 檢查資料庫中的價量資訊
     * @return boolean
     */

    public function checkPriceData() {
        $obj_goods = new StockGoodsService();
        $obj_days = new MarketDaysService();
        $obj_graph = new GoodsGraphService();

        $days = 110; // 設定檢查幾個交易日
        $problem_goods = []; // 記錄有問題的商品
        $problem_dates = []; // 記錄有問題的日期
        
        $good_flag = false;
        $date_flag = false;

        // 取得商品清單
        $goods = $obj_goods->getAllValidGoods();

        // 逐日檢查個股價量資訊是否完整
        $d_date = date('Ymd');
        if ($obj_days->isMarketOpen($d_date) == false) {
            // 今天不是交易日的話，往前推到上一個交易日
            $d_date = $obj_days->calculateMarketDays($d_date, '-', 1);
        }
        while ($days > 0) {
            // 檢查這一天所有商品資料
            foreach($goods as $v) {
                $date_flag = false;
                $good_flag = false;
                
                $ret = $this->checkPriceDataByGoodAndDate($v, $d_date);
                if ($ret) {
                    $date_flag = true;
                    $good_flag = true;
                }
                // 記錄錯誤的交易日
                if ($date_flag) {
                    if (isset($problem_dates[$d_date])) {
                        $problem_dates[$d_date]++;
                    } else {
                        $problem_dates[$d_date] = 1;
                    }
                }
                // 記錄錯誤的個股
                if ($good_flag) {
                    if (isset($problem_goods[$v])) {
                        $problem_goods[$v]++;
                    } else {
                        $problem_goods[$v] = 1;
                    }
                }
                $date_flag = false;
                $good_flag = false;
            }
            // 找上一個交易日
            $d_date = $obj_days->calculateMarketDays($d_date, '-', 1);
            $days--;
        }
        
        // 顯示錯誤資料統計
        arsort($problem_dates);
        arsort($problem_goods);
        echo('資料有問題的日期' . PHP_EOL);
        foreach($problem_dates as $k => $v) {
            echo(sprintf("%s\t%s", $k, $v) . PHP_EOL);
        }
        echo('資料有問題的個股' . PHP_EOL);
        foreach($problem_goods as $k => $v) {
            echo(sprintf("%s\t%s", $k, $v) . PHP_EOL);
        }

        return true;
    }

    /**
     * 指定商品，檢查資料庫中的價量資訊
     * @return boolean
     */

    public function checkPriceDataByGood(string $good) {
        $obj_goods = new StockGoodsService();
        $obj_days = new MarketDaysService();
        $obj_graph = new GoodsGraphService();

        $days = 110; // 設定檢查幾個交易日
        $problem_goods = []; // 記錄有問題的商品
        $problem_dates = []; // 記錄有問題的日期
        
        $good_flag = false;
        $date_flag = false;

        // 逐日檢查個股價量資訊是否完整
        $d_date = date('Ymd');
        if ($obj_days->isMarketOpen($d_date) == false) {
            // 今天不是交易日的話，往前推到上一個交易日
            $d_date = $obj_days->calculateMarketDays($d_date, '-', 1);
        }
        while ($days > 0) {
            // 檢查這一天所有商品資料
            $date_flag = false;
            $good_flag = false;
            
            $ret = $this->checkPriceDataByGoodAndDate($good, $d_date);
            if ($ret) {
                $date_flag = true;
                $good_flag = true;
            }
            // 記錄錯誤的交易日
            if ($date_flag) {
                if (isset($problem_dates[$d_date])) {
                    $problem_dates[$d_date]++;
                } else {
                    $problem_dates[$d_date] = 1;
                }
            }
            // 記錄錯誤的個股
            if ($good_flag) {
                if (isset($problem_goods[$good])) {
                    $problem_goods[$good]++;
                } else {
                    $problem_goods[$good] = 1;
                }
            }
            $date_flag = false;
            $good_flag = false;
            // 找上一個交易日
            $d_date = $obj_days->calculateMarketDays($d_date, '-', 1);
            $days--;
        }
        
        // 顯示錯誤資料統計
        arsort($problem_dates);
        arsort($problem_goods);
        echo('資料有問題的日期' . PHP_EOL);
        foreach($problem_dates as $k => $v) {
            echo(sprintf("%s\t%s", $k, $v) . PHP_EOL);
        }
        echo('資料有問題的個股' . PHP_EOL);
        foreach($problem_goods as $k => $v) {
            echo(sprintf("%s\t%s", $k, $v) . PHP_EOL);
        }

        return true;
    }
    
    /**
     * 檢查指定日期個股的價量資料
     * @param string $good
     * @param string $d_date
     * @return boolean
     */
    public function checkPriceDataByGoodAndDate(string $good, string $d_date) {
        $obj_graph = new GoodsGraphService();

        $good_flag = false;

        // 開盤價
        $data = $obj_graph->getGoodGraphData($good, $d_date, GoodsGraphDataType::PriceOpen());
        if (($data == false) || ($data <= 0)) {
            echo(sprintf("%s\t在%s的開盤價錯誤：%s", $good, $d_date, serialize($data)) . PHP_EOL);
            $good_flag = true;
        }
        // 最高價
        $data = $obj_graph->getGoodGraphData($good, $d_date, GoodsGraphDataType::PriceHigh());
        if (($data == false) || ($data <= 0)) {
            echo(sprintf("%s\t在%s的最高價錯誤：%s", $good, $d_date, serialize($data)) . PHP_EOL);
            $good_flag = true;
        }
        // 最低價
        $data = $obj_graph->getGoodGraphData($good, $d_date, GoodsGraphDataType::PriceLow());
        if (($data == false) || ($data <= 0)) {
            echo(sprintf("%s\t在%s的最低價錯誤：%s", $good, $d_date, serialize($data)) . PHP_EOL);
            $good_flag = true;
        }
        // 收盤價
        $data = $obj_graph->getGoodGraphData($good, $d_date, GoodsGraphDataType::PriceClose());
        if (($data == false) || ($data <= 0)) {
            echo(sprintf("%s\t在%s的收盤價錯誤：%s", $good, $d_date, serialize($data)) . PHP_EOL);
            $good_flag = true;
        }
        // 成交量
        $data = $obj_graph->getGoodGraphData($good, $d_date, GoodsGraphDataType::Volume());
        if (($data == false) || ($data < 0)) {
            echo(sprintf("%s\t在%s的成交量錯誤：%s", $good, $d_date, serialize($data)) . PHP_EOL);
            $good_flag = true;
        }
        return $good_flag;
    }

    /**
     * 檢查價量欄位數字
     * @param string $field_data
     * @param array $row
     * @return boolean
     */
    private function isPriceVolumeFieldInvalid(string $field_data, array $row) {
//        var_dump($field_data);
//        var_dump($row);
//        die();
        if (in_array($field_data, ['-', '--'])) {
            Log::error('異常資料' . json_encode($row));
            return true;
        } else {
            return false;
        }
    }
}
