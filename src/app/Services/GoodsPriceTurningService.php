<?php
/**
 *  計算各種價格變化的service
 *  
 */
namespace App\Services;

use Log;
use Exception;
use App\Services\GoodsPriceUpDownService;
use App\Repositories\PriceDirectionRepository;

class GoodsPriceTurningService {
    
    /**
     * 找股價目前走向
     * @param array $graph_data
     * @return number
     */
    public function findPriceDirection(array $graph_data) {
        $obj_updown = new GoodsPriceUpDownService();
        $threshold = 0.5; // 漲跌幅多少才達到確認方向的門檻
        
        // 找出目前價格作比對基準
        $changed_data = end($graph_data);
        $price_changed = $changed_data['close'];
        echo(date('H:i', strtotime(key($graph_data))) . ' ');
        echo($price_changed . ' ');
        
        $result = 0;
        // 逐一往前比對
        while (true) {
            // 找上一筆，如果沒有上一筆就跳出
            $compare_with = prev($graph_data);
            if ($compare_with == false) {
                break;
            }
            
            // 取出要比對的價格作比對
            $price_base = $compare_with['close'];
            $compare_result = $obj_updown->getPriceDiffByPercent($price_base, $price_changed);
            if (abs($compare_result) >= $threshold) {
                $result = $compare_result;
                echo(date('H:i', strtotime(key($graph_data))) . ' ');
                echo($price_base . PHP_EOL);
                break;
            }
            
        }
        
        return $result;
        
    }
    
    /**
     * 比對並傳回個股是否已出現轉折
     * @param string $goods
     * @param float $direction
     * @return boolean
     */
    public function checkIfDirectionChanged(string $goods, float $direction) {
        $obj_direction = new PriceDirectionRepository();
        $last_direction_row = $obj_direction->getLastDirectionByGoods($goods);
        $changed = false;
        if (sizeof($last_direction_row) == 0) {
            // 資料庫沒撈到相關記錄，視為已變動
            $changed = true;
        } else {
            $last_direction = $last_direction_row[0]->direction;
            if (($last_direction >= 0) && ($direction < 0)) {
                $changed = true;
            } else if (($last_direction <= 0) && ($direction > 0)) {
                $changed = true;
            }
        }
        return $changed;
    }
    
    
    /**
     * 寫入股價轉折變化記錄
     * @param string $goods
     * @param float $direction
     * @return \App\Repositories\unknown
     */
    public function savePriceDirectionChange(string $goods, float $direction) {
        $obj_direction = new PriceDirectionRepository();
        $ret = $obj_direction->updatePriceDirection([
            'code' => $goods,
            'd_date' => date('Y-m-d H:i:s'),
            'direction' => $direction,
            'last_changed' => date('Y-m-d H:i:s'),
        ]);
        return $ret;
    }
    
    /**
     * 回傳漲跌文字
     * @param float $direction
     * @return string
     */
    public function getDirectionText(float $direction) {
        if ($direction > 0) {
            return '漲';
        } else if ($direction < 0) {
            return '跌';
        } else {
            return '平';
        }
    }
    
}
