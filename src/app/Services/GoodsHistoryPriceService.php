<?php
/**
 * 商品價量相關資料處理
 * 這支專門用來取得歷史成交價量，打另一支API
 * 
 * 另一支API https://www.twse.com.tw/exchangeReport/STOCK_DAY?response=json&date=20200617&stockNo=1213&_=1592384560673
 * https://www.tpex.org.tw/web/stock/aftertrading/daily_trading_info/st43_result.php?l=zh-tw&d=109/06&stkno=8044&_=1592405228637
 * 查有問題的資料 SELECT * FROM `prices` WHERE (price_open = (-1) or price_high  = (-1) or price_low  = (-1) or  price_close  = (-1) or volume  = (-1)) and d_date = '2020-06-17 14:30:00'
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\RemoteUrlService;
use App\Services\StockGoodsService;
use App\Services\MarketDaysService;
use App\Repositories\PricesRepository;
use PHPHtmlParser\Dom;

class GoodsHistoryPriceService
{

    /**
     * 取得個股價量資訊
     *
     * @param string $codes
     *            代碼,日期,代碼,日期,代碼,日期,代碼,日期,....
     */
    public function getGoodsHistoryPrice(string $codes)
    {
        $obj_goods = new StockGoodsService();

        // 預先處理參數
        $arr_codes = explode(',', $codes);
        $ok_codes = [];
        for ($i = 0; $i < sizeof($arr_codes); $i = $i + 2) {

            // 略過不完整的參數
            if (isset($arr_codes[$i]) && isset($arr_codes[$i + 1])) {

                // 先看這筆是上市 上櫃 ETF
                $goods_type = $obj_goods->getGoodsType($arr_codes[$i]);
                $goods_name = $obj_goods->getGoodsName($arr_codes[$i]);
                if ($goods_type == '上市') {
                    $goods_type_param = 'tse';
                } else if ($goods_type == '上櫃') {
                    $goods_type_param = 'otc';
                } else if ($goods_type == 'ETF') {
                    $goods_type_param = 'tse';
                } else {
                    $goods_type_param = '';
                }

                if ($goods_type_param != '') {
                    $ok_code = [
                        'code' => $arr_codes[$i],
                        'd_date' => $arr_codes[$i + 1],
                        'goods_type' => $goods_type,
                        'goods_type_param' => $goods_type_param,
                        'goods_name' => $goods_name
                    ];
                    $ok_codes[] = $ok_code;
                }
            }
        }

        // 上市和ETF要打證交所的API，上櫃要打櫃買中心的API，所以要一支支分開處理
        $a = new RemoteUrlService();
        $price_data = [];
        $url_twse = 'https://www.twse.com.tw/exchangeReport/STOCK_DAY?response=json&date=%s&stockNo=%s';
        $url_tpex = 'https://www.tpex.org.tw/web/stock/aftertrading/daily_trading_info/st43_result.php?l=zh-tw&d=%s/%s&stkno=%s';
        foreach ($ok_codes as $v) {
            if (in_array($v['goods_type'], [
                '上市',
                'ETF'
            ])) {
                $url = sprintf($url_twse, $v['d_date'], $v['code']);
            } else if (in_array($v['goods_type'], [
                '上櫃'
            ])) {
                $p_year = (int) substr($v['d_date'], 0, 4) - 1911;
                $p_month = substr($v['d_date'], 4, 2);
                $url = sprintf($url_tpex, $p_year, $p_month, $v['code']);
            }

            // 打api取得資料
            $r = $a->getUrl($url, 21600);

            if (in_array($v['goods_type'], [
                '上市',
                'ETF'
            ])) {
                // 上市或ETF部份
                $r_json = json_decode($r);
                if (isset($r_json->stat) && ($r_json->stat == 'OK')) {
                    foreach ($r_json->data as $v2) {
                        $format = [];
                        // 代號
                        $format['code'] = $v['code'] ?? '';
                        // 名稱
                        $format['name'] = $v['goods_name'] ?? '';
                        // 日期
                        if (preg_match('/([0-9]{3})\/([0-9]{2})\/([0-9]{2})/', $v2[0], $matches)) {
                            $format['date_code'] = ((int) $matches[1] + 1911) . $matches[2] . $matches[3];
                        } else {
                            $format['date_code'] = '';
                        }
                        // 開盤價
                        $format['price_open'] = $v2[3] ?? '';
                        // 最高價
                        $format['price_high'] = $v2[4] ?? '';
                        // 最低價
                        $format['price_low'] = $v2[5] ?? '';
                        // 收盤價
                        $format['price_close'] = $v2[6] ?? ''; // 盤中打API會取得目前股價，昨日收盤價是另一個欄位
                                                                // 成交量
                        if (preg_match('/[0-9,]+/', $v2[1], $matches)) {
                            $format['volume'] = ((int) str_replace(',', '', $matches[0])) / 1000;
                        } else {
                            $format['volume'] = '';
                        }

                        $price_data[] = $format;
                    }
                } else {
                    Log::error('API回應錯誤' . $r);
                }
            } else if (in_array($v['goods_type'], [
                '上櫃'
            ])) {
                // 上櫃部份
                $r_json = json_decode($r);
                foreach ($r_json->aaData as $v2) {
                    $format = [];
                    // 代號
                    $format['code'] = $r_json->stkNo ?? '';
                    // 名稱
                    $format['name'] = $r_json->stkName ?? '';
                    // 日期
                    if (preg_match('/([0-9]{3})\/([0-9]{2})\/([0-9]{2})/', $v2[0], $matches)) {
                        $format['date_code'] = ((int) $matches[1] + 1911) . $matches[2] . $matches[3];
                    } else {
                        $format['date_code'] = '';
                    }
                    // 開盤價
                    $format['price_open'] = $v2[3] ?? '';
                    // 最高價
                    $format['price_high'] = $v2[4] ?? '';
                    // 最低價
                    $format['price_low'] = $v2[5] ?? '';
                    // 收盤價
                    $format['price_close'] = $v2[6] ?? ''; // 盤中打API會取得目前股價，昨日收盤價是另一個欄位
                                                            // 成交量
                    if (preg_match('/[0-9,]+/', $v2[1], $matches)) {
                        $format['volume'] = (int) str_replace(',', '', $matches[0]);
                    } else {
                        $format['volume'] = '';
                    }

                    $price_data[] = $format;
                }
            } else {
                Log::error('找不到商品類別' . var_export($v));
            }
        }
        return $price_data;
    }

    /**
     * 處理api拉下來的資料
     *
     * @param array $price_data
     * @return boolean
     */
    public function saveGoodPrices(array $prices_data)
    {
        if (is_array($prices_data) && sizeof($prices_data) > 0) {
            foreach ($prices_data as $v) {
                $ret = $this->goodPricesToDB($v);
            }
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * 將個股某日的價量資訊寫入db
     *
     * @param array $price_data
     * @return boolean
     */
    public function goodPricesToDB(array $price_data)
    {
        try {
            // 檢查參數
            if ((! isset($price_data['code'])) || (! isset($price_data['date_code'])) || (! isset($price_data['price_open'])) || (! isset($price_data['price_high'])) || (! isset($price_data['price_low'])) || (! isset($price_data['price_close'])) || (! isset($price_data['volume']))) {
                throw new Exception('欄位不可為空' . var_export($price_data));
            }

            // 檢查價量
            if (floatval($price_data['price_open']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_open'], $price_data)) {
                    $price_data['price_open'] = (- 1);
                } else {
                    throw new Exception('開盤價錯誤: ' . $price_data['price_open']);
                }
            }
            if (floatval($price_data['price_high']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_high'], $price_data)) {
                    $price_data['price_high'] = (- 1);
                } else {
                    throw new Exception('最高價錯誤: ' . $price_data['price_high']);
                }
            }
            if (floatval($price_data['price_low']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_low'], $price_data)) {
                    $price_data['price_low'] = (- 1);
                } else {
                    throw new Exception('最低價錯誤: ' . $price_data['price_low']);
                }
            }
            if (floatval($price_data['price_close']) <= 0) {
                if ($this->isPriceVolumeFieldInvalid($price_data['price_close'], $price_data)) {
                    $price_data['price_close'] = (- 1);
                } else {
                    throw new Exception('收盤價錯誤: ' . $price_data['price_close']);
                }
            }
            if (floatval($price_data['volume']) < 0) { // 成交量是有可能為0的
                if ($this->isPriceVolumeFieldInvalid($price_data['volume'], $price_data)) {
                    $price_data['volume'] = (- 1);
                } else {
                    throw new Exception('成交量錯誤: ' . $price_data['volume']);
                }
            }

            // 檢查交易日
            $p_year = (int) substr($price_data['date_code'], 0, 4);
            $p_month = (int) substr($price_data['date_code'], 4, 2);
            $p_day = (int) substr($price_data['date_code'], 6, 2);
            if (! checkdate($p_month, $p_day, $p_year)) {
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
                'volume' => $price_data['volume']
            ];
            return $obj_price->updatePrices($data);
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
        }
    }

    /**
     * 取得價量有問題的個股
     *
     * @return array
     */
    public function getProblemGoods()
    {
        $obj_price = new PricesRepository();
        return $obj_price->getProblemGoods();
    }

    /**
     * 指定月份，抓取所有個股該月份的價量資訊
     *
     * @param string $d_year
     * @param string $d_month
     * @return boolean
     */
    public function getGoodsHistoryPriceByMonth(string $d_year, string $d_month)
    {
        // 抓取目前有效個股
        $obj_goods = new StockGoodsService();
        $goods = $obj_goods->getAllValidGoods();

        // 取得指定月份最後一個交易日
        $obj_days = new MarketDaysService();
        $last_day = $obj_days->getLastMarketOpenDay($d_year . $d_month . '01');

        // 個別取得股價並寫入資料庫
        foreach ($goods as $v) {
            $codes = $v . ',' . $last_day;
            echo ($codes . PHP_EOL);
            $prices_data = $this->getGoodsHistoryPrice($codes);
            $db_result = $this->saveGoodPrices($prices_data);
        }

        return true;
    }

    /**
     * 檢查價量欄位數字
     *
     * @param string $field_data
     * @param array $row
     * @return boolean
     */
    private function isPriceVolumeFieldInvalid(string $field_data, array $row)
    {
        if (in_array($field_data, [
            '-',
            '--'
        ])) {
            Log::error('異常資料' . json_encode($row));
            return true;
        } else {
            return false;
        }
    }
}
