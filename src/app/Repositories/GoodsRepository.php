<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Goods;

class GoodsRepository {
    /**
     * 新增或更新個股商品資料
     * @param string $code
     * @param string $name
     * @param string $type
     * @return unknown
     */
    public function updateGoods(string $code, string $name, string $type) {
        $goods = Goods::firstOrNew(
            [
                'code' => $code
                
            ]
        );
        $goods->name = $name;
        $goods->type = $type;
        $goods->disabled = 0;
        return $goods->save();
        
    }
    
    /**
     * 傳入商品代碼，傳回是上市，上櫃或ETF
     * @param string $code
     * @return string
     */
    public function getGoodsType(string $code) {
        $good = Goods::where('code', $code)->where('disabled', '<>', '1')->first();
        if ($good) {
            return $good->type;
        } else {
            return false;
        }
    }
    
    /**
     * 傳入商品代碼，傳回名稱
     * @param string $code
     * @return string
     */
    public function getGoodsName(string $code) {
        $good = Goods::where('code', $code)->where('disabled', '<>', '1')->first();
        if ($good) {
            return $good->name;
        } else {
            return false;
        }
    }
    
    /**
     * 取得所有有效個股
     * @return unknown|boolean
     */
    public function getAllValidGoods() {
        $good = Goods::where('disabled', '<>', '1')->orderBy('code', 'asc')->get();
        if ($good) {
            return $good;
        } else {
            return false;
        }
    }
}