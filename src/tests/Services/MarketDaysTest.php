<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\MarketDaysService;

class MarketDaysTest extends TestCase
{
    
    /**
     * 有開盤的日期
     */
    public function testOpenMarketDays() {
        $obj = new MarketDaysService();
        
        $ret = $obj->isMarketOpen('20200102'); // 元旦
        $this->assertTrue($ret);
        $ret = $obj->isMarketOpen('20200130'); // 開紅盤
        $this->assertTrue($ret);
    }
    
    /**
     * 不開盤的日期
     */
    public function testClosedMarketDays() {
        $obj = new MarketDaysService();
        
        $ret = $obj->isMarketOpen('20200101'); // 元旦
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200121'); // 過年前
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200122'); // 過年前
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200123'); // 過年前
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200215'); // 補班日
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200228'); // 228
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200402'); // 清明連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200403'); // 清明連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200404'); // 清明連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200501'); // 勞動節
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200620'); // 補班日
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200625'); // 端午連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200626'); // 端午連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20200926'); // 補班日
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20201001'); // 中秋連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20201002'); // 中秋連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20201009'); // 國慶連假
        $this->assertFalse($ret);
        $ret = $obj->isMarketOpen('20201010'); // 國慶連假
        $this->assertFalse($ret);
    }
    
    /**
     * 計算交易日
     */
    public function testCalculateMarketDays() {
        $obj = new MarketDaysService();
        
        // 中間沒放假
        $new_date = $obj->calculateMarketDays('20200622', '+', 1);
        $this->assertEquals('20200623', $new_date);
        $new_date = $obj->calculateMarketDays('20200617', '-', 2);
        $this->assertEquals('20200615', $new_date);
        
        // 補班日不開盤
        $new_date = $obj->calculateMarketDays('20200618', '+', 2);
        $this->assertEquals('20200622', $new_date);
        $new_date = $obj->calculateMarketDays('20200623', '-', 3);
        $this->assertEquals('20200618', $new_date);
        
        // 連假不開盤
        $new_date = $obj->calculateMarketDays('20200624', '+', 1);
        $this->assertEquals('20200629', $new_date);
        $new_date = $obj->calculateMarketDays('20200630', '-', 2);
        $this->assertEquals('20200624', $new_date);
        
        // 假期中找下一個交易日
        $new_date = $obj->calculateMarketDays('20200626', '-', 1);
        $this->assertEquals('20200624', $new_date);
        $new_date = $obj->calculateMarketDays('20200625', '+', 1);
        $this->assertEquals('20200629', $new_date);
    }
    
    /**
     * 檢查收盤時間
     */
    public function testMarketClosed() {
        $obj = new MarketDaysService();
        $ret = $obj->isCurrentlyMarketClosed();
        $this->assertEquals(true, $ret);
        
    }
    
}
