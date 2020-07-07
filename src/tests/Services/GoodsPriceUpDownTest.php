<?php
/**
 * 測試漲跌幅，tick等
 * todo 用實際的例子來測試
 */

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\GoodsPriceUpDownService;

class GoodsPriceUpDownTest extends TestCase
{
    /**
     * 測試漲跌幅計算
     */
    public function testPriceUpDownByPercent() {
        $obj_updown = new GoodsPriceUpDownService();
        // 10元漲10%應為11元
        $ret = $obj_updown->getPriceUpDownByPercent(10, 10);
        $this->assertEquals(11, $ret);
        
        // 10元跌10%應為9元
        $ret = $obj_updown->getPriceUpDownByPercent(10, -10);
        $this->assertEquals(9, $ret);
        
    }

    public function testPriceDiffByPercent() {
        $obj_updown = new GoodsPriceUpDownService();
        // 10元和11元為漲10%的差距
        $ret = $obj_updown->getPriceDiffByPercent(10, 11);
        $this->assertEquals(10, $ret);
        
        // 10元和9元為跌10%的差距
        $ret = $obj_updown->getPriceDiffByPercent(10, 9);
        $this->assertEquals(-10, $ret);
        
    }
    
    /**
     * 測試漲跌tick計算
     */
    public function testPriceUpDownByTick() {
        $obj_updown = new GoodsPriceUpDownService();
        // 個股30元漲2個tick應為30.1元
        $ret = $obj_updown->getTickedPrice('2884', 30, 2);
        $this->assertEquals(30.1, $ret);
        
        // 個股30元跌3個tick應為29.85元
        $ret = $obj_updown->getTickedPrice('2884', 30, -3);
        $this->assertEquals(29.85, $ret);
        
        // ETF30元漲2個tick應為30.02元
        $ret = $obj_updown->getTickedPrice('0050', 30, 2);
        $this->assertEquals(30.02, $ret);
        
        // ETF30元跌3個tick應為29.97元
        $ret = $obj_updown->getTickedPrice('0050', 30, -3);
        $this->assertEquals(29.97, $ret);
        
    }
    
    /**
     * 測試漲跌tick差距計算
     */
    public function testPriceDiffByTick() {
        $obj_updown = new GoodsPriceUpDownService();
        // 個股30元漲到30.1元，應為漲2個tick
        $ret = $obj_updown->getTicksBetweenPrice('2884', 30, 30.1);
        $this->assertEquals(2, $ret);
        
        // 個股30元跌到29.85元，應為跌3個tick
        $ret = $obj_updown->getTicksBetweenPrice('2884', 30, 29.85);
        $this->assertEquals(-3, $ret);
        
        // ETF30元漲到30.02元，應為漲2個tick
        $ret = $obj_updown->getTicksBetweenPrice('0050', 30, 30.02);
        $this->assertEquals(2, $ret);
        
        // ETF30元跌到29.97元，應為跌3個tick
        $ret = $obj_updown->getTicksBetweenPrice('0050', 30, 29.97);
        $this->assertEquals(-3, $ret);
        
    }
    
    /**
     * 測試去除小數點多餘位數
     */
    public function testRoundedPrice() {
        $obj_updown = new GoodsPriceUpDownService();
        // 個股30.123元，應為30.12元
        $ret = $obj_updown->getRoundedPrice('2884', 30.123);
        $this->assertEquals(30.12, $ret);
        
        // ETF30.123元，應為30.12元
        $ret = $obj_updown->getRoundedPrice('0050', 30.123);
        $this->assertEquals(30.12, $ret);
        
    }
    
    
}
