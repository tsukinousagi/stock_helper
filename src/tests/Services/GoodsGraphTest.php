<?php
/**
 * 線圖價量的各種test
 */

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\GoodsGraphService;
use App\Enums\GoodsGraphDataType;
use App\Enums\GoodsGraphMADirection;

class GoodsGraphTest extends TestCase
{
    /**
     * 玉山金 2020年6月23日
     */
    public function testGetPrices288420200623PriceOpen()
    {
        $obj_service = new GoodsGraphService();
        // 開盤價
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::PriceOpen());
        $this->assertEquals(27.8, $result);
    }
    public function testGetPrices288420200623PriceHigh()
    {
        $obj_service = new GoodsGraphService();
        // 最高價
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::PriceHigh());
        $this->assertEquals(28, $result);
    }
    public function testGetPrices288420200623PriceLow()
    {
        $obj_service = new GoodsGraphService();
        // 最低價
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::PriceLow());
        $this->assertEquals(27.7, $result);
    }
    public function testGetPrices288420200623PriceClose()
    {
        $obj_service = new GoodsGraphService();
        // 收盤價
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::PriceClose());
        $this->assertEquals(27.95, $result);
    }
    public function testGetPrices288420200623PriceVolume()
    {
        $obj_service = new GoodsGraphService();
        // 成交量
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::Volume());
        $this->assertEquals(18815, $result);
    }
    public function testGetPrices288420200623Price5MA()
    {
        $obj_service = new GoodsGraphService();
        // 5日均價27.92向下
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::Price5MA());
        $this->assertEquals(27.92, $result);
    }
    public function testGetPrices288420200623Price10MA()
    {
        $obj_service = new GoodsGraphService();
        // 10日均價27.92向下
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::Price10MA());
        $this->assertEquals(27.92, $result);
    }
    public function testGetPrices288420200623Price20MA()
    {
        $obj_service = new GoodsGraphService();
        // 20日均價27.74向上
        $result = $obj_service->getGoodGraphData('2884', '20200623', GoodsGraphDataType::Price20MA());
        $this->assertEquals(27.74, $result);
    }

    /**
     * 鴻海 2020年6月23日
     */
    public function testGetPrices231720200623PriceOpen()
    {
        $obj_service = new GoodsGraphService();
        // 開盤價
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::PriceOpen());
        $this->assertEquals(79.6, $result);
    }
    public function testGetPrices231720200623PriceHigh()
    {
        $obj_service = new GoodsGraphService();
        // 最高價
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::PriceHigh());
        $this->assertEquals(80.7, $result);
    }
    public function testGetPrices231720200623PriceLow()
    {
        $obj_service = new GoodsGraphService();
        // 最低價
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::PriceLow());
        $this->assertEquals(79.3, $result);
    }
    public function testGetPrices231720200623PriceClose()
    {
        $obj_service = new GoodsGraphService();
        // 收盤價
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::PriceClose());
        $this->assertEquals(80.2, $result);
    }
    public function testGetPrices231720200623PriceVolume()
    {
        $obj_service = new GoodsGraphService();
        // 成交量
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::Volume());
        $this->assertEquals(45582, $result);
    }
    public function testGetPrices231720200623Price5MA()
    {
        $obj_service = new GoodsGraphService();
        // 5日均價78.92向上
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::Price5MA());
        $this->assertEquals(78.92, $result);
    }
    public function testGetPrices231720200623Price10MA()
    {
        $obj_service = new GoodsGraphService();
        // 10日均價78.65向上
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::Price10MA());
        $this->assertEquals(78.65, $result);
    }
    public function testGetPrices231720200623Price20MA()
    {
        $obj_service = new GoodsGraphService();
        // 20日均價78.02向上
        $result = $obj_service->getGoodGraphData('2317', '20200623', GoodsGraphDataType::Price20MA());
        $this->assertEquals(78.02, $result);
    }
}
