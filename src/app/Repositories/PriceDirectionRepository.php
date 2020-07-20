<?php
namespace App\Repositories;

use Log;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PriceDirection;
use Carbon\Carbon;

class PriceDirectionRepository {
    /**
     * 新增個股漲跌變化記錄
     * @param array $prices_data
     * @return unknown
     */
    public function updatePriceDirection(array $price_direction_data) {
        try {
            // 以商品代號和日期為檢查基準
            $price_direction = PriceDirection::firstOrNew(
                [
                    'code' => $price_direction_data['code'],
                    'd_date' => $price_direction_data['d_date'],
                    'direction' => $price_direction_data['direction'],
                    'last_changed' => $price_direction_data['last_changed'],
                ]
            );
            return $price_direction->save();
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
        }
        
    }
    
    public function getLastDirectionByGoods(string $goods) {
        try {
            // 以商品代號和日期為檢查基準
            $price_direction = PriceDirection::where('code', $goods)
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get();
            return $price_direction;
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
        }
    }
    
}