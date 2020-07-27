<?php
/**
 * 查交易日相關資訊的Service
 * Ref: https://www.twse.com.tw/zh/holidaySchedule/holidaySchedule
 */
namespace App\Services;

use Log;
use Exception;


class MarketDaysService {
    
    public function isTodayMarketOpen() {
        return $this->isMarketOpen(date('Ymd'));
    }
    
    /**
     * 查詢是否為交易日
     * @param string $d_date
     * @return boolean
     */
    public function isMarketOpen(string $d_date) {

        // 分離年月日
        $d_year = substr($d_date, 0, 4);
        $d_month = substr($d_date, 4, 2);
        $d_day = substr($d_date, 6, 2);

        // 先查是否為週六週日
        $check_week = date('w', mktime(0, 0, 0, $d_month, $d_day, $d_year));
        if (in_array($check_week, ['0', '6'])) {
            return false;
        } else {
            // 再查是否為特定不開盤的日子
            if ($d_year == '2020') {
                if (in_array($d_month . $d_day, [
                    '0101',
                    '0121',
                    '0122',
                    '0123',
                    '0124',
                    '0125',
                    '0126',
                    '0127',
                    '0128',
                    '0129',
                    '0228',
                    '0402',
                    '0403',
                    '0404',
                    '0501',
                    '0625',
                    '0626',
                    '1001',
                    '1002',
                    '1009',
                    '1010',
                    
                ])) {
                    return false;
                    
                } else {
                    return true;
                }
            }
        }
        
    }
    
    /**
     * 往前或往後推算交易日
     * @param string $d_date
     * @param string $direction
     * @param int $days
     * @return string
     */
    public function calculateMarketDays(string $d_date, string $direction, int $days) {
        $remaining_days = $days;
        $new_date = $d_date;
        while ($remaining_days > 0) {
            $new_date = $this->calculateDay($new_date, $direction);
            if ($this->isMarketOpen($new_date)) {
                $remaining_days--;
            }
        }
        return $new_date;
    }
    
    /**
     * 取得該月最後一個交易日
     * @param string $d_date
     * @return \App\Services\string
     */
    public function getLastMarketOpenDay(string $d_date) {
        // 分離年月日
        $d_year = substr($d_date, 0, 4);
        $d_month = substr($d_date, 4, 2);
        $d_day = substr($d_date, 6, 2);
        $new_date = date('Ymt', mktime(0, 0, 0, $d_month, $d_day, $d_year));
        while($this->isMarketOpen($new_date) == false) {
            $new_date = $this->calculateDay($new_date, '-');
        }
        
        return $new_date;
    }
    
    
    /**
     * 加減一天，不考慮交易日
     * @param string $d_date
     * @param string $direction
     * @return string
     */
    private function calculateDay(string $d_date, string $direction) {
        // 分離年月日
        $d_year = substr($d_date, 0, 4);
        $d_month = substr($d_date, 4, 2);
        $d_day = substr($d_date, 6, 2);
        
        // 算timestamp
        $ts = mktime(0, 0, 0, $d_month, $d_day, $d_year);
        
        // 加減
        if ($direction == '+') {
            $ts += 86400;
        } else if ($direction == '-') {
            $ts -= 86400;
        } else {
            return false;
        }
        
        // 組回日期
        return date('Ymd', $ts);
    }
    
    /**
     * 收盤了沒？
     * @return boolean
     */
    public function isCurrentlyMarketClosed() {
        if ($this->isTodayMarketOpen()) {
            if (time() < mktime(13, 35, 00)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

}
