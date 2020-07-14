<?php
/**
 * 假的富果API，開發測試用
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FakeFugleAPIService;

class FakeFugleAPIController extends Controller
{
    /**
     * 取即時價量資料的假API入口
     * @param string $goods
     * @return string
     */
    public function getGoodsRealtimeData(Request $request) {
        $obj_fake = new FakeFugleAPIService();
        $goods = $request->symbolId;
        $ret = $obj_fake->getRealtimeChartData($goods);
        return $ret;
    }
}
