<?php
/**
 * 記錄個股每分鐘價量的table
 */
namespace App\Repositories;

use Log;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PricesMinute;
use Carbon\Carbon;

class PricesMinuteRepository {
    /**
     * 新增或更新個股在某個日期的價量資料
     * @param array $prices_data
     * @return unknown
     */
    public function updatePrices(array $price_data) {
        try {
            // 以商品代號和日期為檢查基準
            $price = PricesMinute::firstOrNew(
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
//        var_dump(Carbon::now()->subDays(180)->toDateTimeString());
//        \DB::enableQueryLog();
        $problem_goods = PricesMinute::where(function ($query) {
            $query->where('price_open', '=', -1)
            ->orWhere('price_high', '=', -1)
            ->orWhere('price_low', '=', -1)
            ->orWhere('price_close', '=', -1)
            ->orWhere('volume', '=', -1);
        }) 
            // 180天前的資料就算了
            ->whereRaw("`d_date` >= '" . Carbon::now()->subDays(180)->toDateTimeString() . "'")
            ->select('d_date', 'code')
            ->orderBy('d_date', 'desc')
            ->orderBy('code', 'asc')
            ->get();
//        var_dump(\DB::getQueryLog());
        return $problem_goods;
    }
    
    /**
     * 取得特定個股特定日期價量
     * @param string $code
     * @param string $d_date
     * @return number
     */
    public function getPriceData(string $code, string $d_date) {
        $date_formatted = sprintf('%s-%s-%s 14:30:00',
            substr($d_date, 0, 4),
            substr($d_date, 4, 2),
            substr($d_date, 6, 2));

        $row = PricesMinute::where('code', $code)
            ->where('d_date', $date_formatted)
            ->select('price_open', 'price_high', 'price_low', 'price_close', 'volume')
            ->get();

        return $row;
        
    }
}