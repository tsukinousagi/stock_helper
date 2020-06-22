<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\GoodsHistoryPriceService;

class GetGoodsHistoryPriceTest extends TestCase
{
    public function testGetGoodsHistoryPriceTwse() {
        $this->markTestSkipped();
        $a = new GoodsHistoryPriceService();
        $r = $a->getGoodsHistoryPrice('2884,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsHistoryPriceTpex() {
        $this->markTestSkipped();
        $a = new GoodsHistoryPriceService();
        $r = $a->getGoodsHistoryPrice('8044,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsHistoryPriceEtf() {
        $this->markTestSkipped();
        $a = new GoodsHistoryPriceService();
        $r = $a->getGoodsHistoryPrice('00632R,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsHistoryPriceMultiDays() {
        $a = new GoodsHistoryPriceService();
        $r = $a->getGoodsHistoryPrice('2884,20200608,2884,20200609,2884,20200610,2884,20200611,2884,20200612');
        $s = $a->saveGoodPrices($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsHistoryPriceMultiDaysETF() {
        $a = new GoodsHistoryPriceService();
        $r = $a->getGoodsHistoryPrice('0050,20200608,0050,20200609,0050,20200610,0050,20200611,0050,20200612');
        $s = $a->saveGoodPrices($r);
        $this->assertTrue(True);
    }
}
