<?php
namespace App\Repositories;

use Log;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Prices;

class PricesRepository {
    /**
     * 新增或更新個股在某個日期的價量資料
     * @param array $prices_data
     * @return unknown
     */
    public function updatePrices(array $price_data) {
        try {
            // 以商品代號和日期為檢查基準
            $price = Prices::firstOrNew(
                [
                    'code' => $price_data['code'],
                    'd_date' => $price_data['d_date'],
                ]
            );
            $price->price_open = $price_data['price_open'];
            $price->price_high = $price_data['price_high'];
            $price->price_low = $price_data['price_low'];
            $price->price_close = $price_data['price_close'];
            $price->volume = $price_data['volume'];
            return $price->save();
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
        }
        
    }
    

    /**
     * 取得價量有問題的個股
     * @return array
     */
    public function getProblemGoods() {
        $problem_goods = Prices::where('price_open', '=', -1)
            ->orWhere('price_high', '=', -1)
            ->orWhere('price_low', '=', -1)
            ->orWhere('price_close', '=', -1)
            ->orWhere('volume', '=', -1)
            ->select('d_date', 'code')
            ->orderBy('d_date', 'desc')
            ->orderBy('code', 'asc')
            ->get();
        return $problem_goods;
    }
}