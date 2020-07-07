<?php
/**
 *  計算各種價格變化的service
 *  
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\StockGoodsService;

class GoodsPriceUpDownService
{
    
    /**
     * 算股價漲跌後是多少
     * @param float $price
     * @param float $percent
     * @return number
     */
    public function getPriceUpDownByPercent(float $price, float $percent) {
        $ret = $price * (1 + $percent / 100);
        return $ret;
    }

    /**
     * 算兩個股價間的漲跌幅度
     * @param float $price_base
     * @param float $price_changed
     * @return number
     */
    public function getPriceDiffByPercent(float $price_base, float $price_changed) {
        $ret = ($price_changed - $price_base) / $price_base * 100;
        return $ret;
    }
    
    /**
     * 算股價上下幾檔之後應該是多少
     * 個股和ETF漲跌檔數不同，在function裡判斷處理
     * Ref: https://www.twse.com.tw/zh/page/products/trading/introduce.html
     * @param string $goods
     * @param float $price
     * @param int $tick
     * @return boolean|number
     */
    public function getTickedPrice(string $goods, float $price, int $tick) {
        // 查個股類別
        $obj_goods = new StockGoodsService();
        $goods_type = $obj_goods->getGoodsType($goods);
        
        // 計算漲跌幅tick
        $new_price = false;
        if (in_array($goods_type, ['上市', '上櫃'])) {
            if (($price >= 0.01) && ($price < 5)) {
                $new_price = $price + ($tick * 0.01);
            } else if (($price >= 5) && ($price < 10)) {
                $new_price = $price + ($tick * 0.01);
            } else if (($price >= 10) && ($price < 50)) {
                $new_price = $price + ($tick * 0.05);
            } else if (($price >= 50) && ($price < 100)) {
                $new_price = $price + ($tick * 0.1);
            } else if (($price >= 100) && ($price < 150)) {
                $new_price = $price + ($tick * 0.5);
            } else if (($price >= 150) && ($price < 500)) {
                $new_price = $price + ($tick * 0.5);
            } else if (($price >= 500) && ($price < 1000)) {
                $new_price = $price + ($tick * 1);
            } else if ($price >= 1000) {
                $new_price = $price + ($tick * 5);
            }
        } else if (in_array($goods_type, ['ETF'])) {
            if (($price >= 0.01) && ($price < 5)) {
                $new_price = $price + ($tick * 0.01);
            } else if (($price >= 5) && ($price < 10)) {
                $new_price = $price + ($tick * 0.01);
            } else if (($price >= 10) && ($price < 50)) {
                $new_price = $price + ($tick * 0.01);
            } else if (($price >= 50) && ($price < 100)) {
                $new_price = $price + ($tick * 0.05);
            } else if (($price >= 100) && ($price < 150)) {
                $new_price = $price + ($tick * 0.05);
            } else if (($price >= 150) && ($price < 500)) {
                $new_price = $price + ($tick * 0.05);
            } else if (($price >= 500) && ($price < 1000)) {
                $new_price = $price + ($tick * 0.05);
            } else if ($price >= 1000) {
                $new_price = $price + ($tick * 0.05);
            }
        }
        return $new_price;
    }
    
    
    /**
     * 算兩個股價之間相差幾檔
     * 個股和ETF漲跌檔數不同，在function裡判斷處理
     * Ref: https://www.twse.com.tw/zh/page/products/trading/introduce.html
     * @param string $goods
     * @param float $price_base
     * @param float $price_changed
     * @return number
     */
    public function getTicksBetweenPrice(string $goods, float $price_base, float $price_changed) {
        // 查個股類別
        $obj_goods = new StockGoodsService();
        $goods_type = $obj_goods->getGoodsType($goods);
        
        // 計算漲跌幅tick
        $ticks = false;
        if (in_array($goods_type, ['上市', '上櫃'])) {
            if (($price_base >= 0.01) && ($price_base < 5)) {
                $ticks = ($price_changed - $price_base) / 0.01;
            } else if (($price_base >= 5) && ($price_base < 10)) {
                $ticks = ($price_changed - $price_base) / 0.01;
            } else if (($price_base >= 10) && ($price_base < 50)) {
                $ticks = ($price_changed - $price_base) / 0.05;
            } else if (($price_base >= 50) && ($price_base < 100)) {
                $ticks = ($price_changed - $price_base) / 0.1;
            } else if (($price_base >= 100) && ($price_base < 150)) {
                $ticks = ($price_changed - $price_base) / 0.5;
            } else if (($price_base >= 150) && ($price_base < 500)) {
                $ticks = ($price_changed - $price_base) / 0.5;
            } else if (($price_base >= 500) && ($price_base < 1000)) {
                $ticks = ($price_changed - $price_base) / 1;
            } else if ($price_base >= 1000) {
                $ticks = ($price_changed - $price_base) / 5;
            }
        } else if (in_array($goods_type, ['ETF'])) {
            if (($price_base >= 0.01) && ($price_base < 5)) {
                $ticks = ($price_changed - $price_base) / 0.01;
            } else if (($price_base >= 5) && ($price_base < 10)) {
                $ticks = ($price_changed - $price_base) / 0.01;
            } else if (($price_base >= 10) && ($price_base < 50)) {
                $ticks = ($price_changed - $price_base) / 0.01;
            } else if (($price_base >= 50) && ($price_base < 100)) {
                $ticks = ($price_changed - $price_base) / 0.05;
            } else if (($price_base >= 100) && ($price_base < 150)) {
                $ticks = ($price_changed - $price_base) / 0.05;
            } else if (($price_base >= 150) && ($price_base < 500)) {
                $ticks = ($price_changed - $price_base) / 0.05;
            } else if (($price_base >= 500) && ($price_base < 1000)) {
                $ticks = ($price_changed - $price_base) / 0.05;
            } else if ($price_base >= 1000) {
                $ticks = ($price_changed - $price_base) / 0.05;
            }
        }
        return $ticks;
    }
    
    /**
     * 去除股價後方小數點多餘位數
     * @param string $goods
     * @param float $price
     * @return number
     */
    public function getRoundedPrice(string $goods, float $price) {
        // 查個股類別
        $obj_goods = new StockGoodsService();
        $goods_type = $obj_goods->getGoodsType($goods);
        
        // 去除小數點後多餘位數
        $new_price = false;
        if (in_array($goods_type, ['上市', '上櫃'])) {
            if (($price >= 0.01) && ($price < 5)) {
                $new_price = round($price, 2);
            } else if (($price >= 5) && ($price < 10)) {
                $new_price = round($price, 2);
            } else if (($price >= 10) && ($price < 50)) {
                $new_price = round($price, 2);
            } else if (($price >= 50) && ($price < 100)) {
                $new_price = round($price, 1);
            } else if (($price >= 100) && ($price < 150)) {
                $new_price = round($price, 1);
            } else if (($price >= 150) && ($price < 500)) {
                $new_price = round($price, 1);
            } else if (($price >= 500) && ($price < 1000)) {
                $new_price = round($price);
            } else if ($price >= 1000) {
                $new_price = round($price);
            }
        } else if (in_array($goods_type, ['ETF'])) {
            if (($price >= 0.01) && ($price < 5)) {
                $new_price = round($price, 2);
            } else if (($price >= 5) && ($price < 10)) {
                $new_price = round($price, 2);
            } else if (($price >= 10) && ($price < 50)) {
                $new_price = round($price, 2);
            } else if (($price >= 50) && ($price < 100)) {
                $new_price = round($price, 2);
            } else if (($price >= 100) && ($price < 150)) {
                $new_price = round($price, 2);
            } else if (($price >= 150) && ($price < 500)) {
                $new_price = round($price, 2);
            } else if (($price >= 500) && ($price < 1000)) {
                $new_price = round($price, 2);
            } else if ($price >= 1000) {
                $new_price = round($price, 2);
            }
        }
        return $new_price;
    }
}
