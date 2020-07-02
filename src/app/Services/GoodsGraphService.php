<?php
/**
 * 查商品價量相關資訊的Service
 */
namespace App\Services;

use Log;
use Exception;
use App\Enums\GoodsGraphDataType;
use App\Enums\GoodsGraphMADirection;
use Illuminate\Support\Facades\Redis;
use App\Repositories\PricesRepository;
use App\Services\MarketDaysService;


class GoodsGraphService {
    
    private $obj_redis;
    private $field_map;

    public function __construct() {
        // 設定對應的db欄位
        $this->field_map = [
            GoodsGraphDataType::PriceOpen => 'price_open',
            GoodsGraphDataType::PriceHigh => 'price_high',
            GoodsGraphDataType::PriceLow => 'price_low',
            GoodsGraphDataType::PriceClose => 'price_close',
            GoodsGraphDataType::Volume => 'volume',
        ];
        $this->obj_redis = Redis::connection();
    }
    /**
     * 查個股相關價量資訊
     * @param string $good
     * @param string $d_date
     * @param GoodsGraphDataType $data_type
     * @return number
     */
    public function getGoodGraphData(string $good, string $d_date, GoodsGraphDataType $data_type) {
        try {

            // 先去redis撈有沒有此價量
            $redis_key = 'GGD' . $good . $d_date . 'GDT' . $data_type;
            $field_data = $this->obj_redis->get($redis_key);
            if ($field_data == null) {
                // 如果是開盤價、最高價、最低價、收盤價、成交量，直接去db撈
                if (in_array($data_type->value, [
                    GoodsGraphDataType::PriceOpen,
                    GoodsGraphDataType::PriceHigh,
                    GoodsGraphDataType::PriceLow,
                    GoodsGraphDataType::PriceClose,
                    GoodsGraphDataType::Volume,
                ])) {
                    // 先去redis撈看看有沒有這筆「完整」的價量
                    $redis_row_key = 'P' . $good . $d_date;
                    $row_data = $this->obj_redis->get($redis_row_key);
                    if ($row_data == null) {
                        // 去db撈並寫進redis
                        $row_data = $this->getPriceFromDB($good, $d_date);
                        $this->obj_redis->set($redis_row_key, serialize($row_data));
                    } else {
                        $row_data = unserialize($row_data);
                    }
                    
                    // 取特定價量欄位
                    $this_field = $this->field_map[$data_type->value];
                    $field_data = $row_data[$this_field];
                    
                    // 成交量四捨五入
                    if ($data_type->value == GoodsGraphDataType::Volume) {
                        $field_data = round($field_data);
                    }

                } else if ($data_type->value == GoodsGraphDataType::Price5MA) {
                    // 5日均價
                    $field_data = $this->getGoodMAPrice($good, $d_date, 5);
                } else if ($data_type->value == GoodsGraphDataType::Price10MA) {
                    // 10日均價
                    $field_data = $this->getGoodMAPrice($good, $d_date, 10);
                } else if ($data_type->value == GoodsGraphDataType::Price20MA) {
                    // 20日均價
                    $field_data = $this->getGoodMAPrice($good, $d_date, 20);
                } else if ($data_type->value == GoodsGraphDataType::Price5MADirection) {
                    // 5日均價方向
                    $field_data = $this->getGoodMADirection($good, $d_date, 5);
                } else if ($data_type->value == GoodsGraphDataType::Price10MADirection) {
                    // 10日均價方向
                    $field_data = $this->getGoodMADirection($good, $d_date, 10);
                } else if ($data_type->value == GoodsGraphDataType::Price20MADirection) {
                    // 20日均價方向
                    $field_data = $this->getGoodMADirection($good, $d_date, 20);
                }
                // 寫入redis前先檢查
                if (isset($field_data) && ($field_data <> null)) {
                    $this->obj_redis->set($redis_key, $field_data);
                } else {
                    throw new Exception('取不到價量資料' . serialize($good) . serialize($d_date) . serialize($data_type));
                }
            }
            
            return $field_data;
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            return false;
        }
    }
    

    /**
     * 取得個股某日的均價
     * @param string $good
     * @param string $d_date
     * @param int $ma_days
     * @return number
     */
    public function getGoodMAPrice(string $good, string $d_date, int $ma_days) {
        $obj_days = new MarketDaysService();

        try {
            // 記錄各日收盤價
            $price_each_day = [];

            // 查詢每日收盤價
            $remaining_days = $ma_days;
            $previous_day = $d_date;
            while($remaining_days > 0) {
                $price_each_day[(string) $previous_day] = (float) $this->getGoodGraphData($good, $previous_day, GoodsGraphDataType::PriceClose());
                // 查上一個交易日
    //            var_dump($previous_day);
                $previous_day = $obj_days->calculateMarketDays($previous_day, '-', 1);
                $remaining_days--;
            }

            // 算均價
            if (count($price_each_day) <> $ma_days) {
                throw new Exception('均價計算發生錯誤' . $good . ':' . $d_date);
            } else {
                $ma = array_sum($price_each_day) / $ma_days;
                Log::info($good . ' ' . $d_date . ' ' . $ma_days . ' ' . $ma);
                $fix_digits = 2;
                // todo 依股價高低決定round到多少小數位
                /*
                $pow = pow(10, $fix_digits);
                $ma = floor($ma * $pow) / $pow;
                */
                $ma = round($ma, $fix_digits);
                return $ma;
            }
        } catch (Exception $e) {
            Log::error($e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    /**
     * 取得個股某日均線方向
     * @param string $good
     * @param string $d_date
     * @param int $ma_days
     * @return \App\Enums\GoodsGraphMADirection
     */
    public function getGoodMADirection(string $good, string $d_date, int $ma_days) {
        $obj_days = new MarketDaysService();
        // 取指定日期的均價
        $this_ma = $this->getGoodMAPrice($good, $d_date, $ma_days);
        // 取前一個交易日
        $previous_day = $obj_days->calculateMarketDays($d_date, '-', 1);
        // 取前一個交易日的均價
        $previous_ma = $this->getGoodMAPrice($good, $previous_day, $ma_days);
        // 比對
        if ($previous_ma < $this_ma) {
            $direction = GoodsGraphMADirection::MADUp();
        } else if ($previous_ma > $this_ma) {
            $direction = GoodsGraphMADirection::MADDown();
        } else if ($previous_ma == $this_ma) {
            $direction = GoodsGraphMADirection::MADFlat();
        } else {
            $direction = false;
        }
        
        return $direction;
    }
    
    private function getPriceFromDB(string $good, string $d_date) {
        try {
            $obj_price = new PricesRepository();
            $row = $obj_price->getPriceData($good, $d_date);
            if ($row) {
                $row_data = $row->toArray();
                $row_data = $row_data[0];
                return $row_data;
            } else {
                throw new Exception('取價量資料發生錯誤' . $good . ':' . $d_date);
            }
        } catch (Exception $e) {
            Log::error($e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }
}
