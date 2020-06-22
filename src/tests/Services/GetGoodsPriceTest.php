<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\GoodsPriceService;

class GetGoodsPriceTest extends TestCase
{
    public function testGetGoodsPriceTwse() {
        $this->markTestSkipped();
        $a = new GoodsPriceService();
        $r = $a->getGoodsPrice('2884,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsPriceTpex() {
        $this->markTestSkipped();
        $a = new GoodsPriceService();
        $r = $a->getGoodsPrice('8044,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsPriceEtf() {
        $this->markTestSkipped();
        $a = new GoodsPriceService();
        $r = $a->getGoodsPrice('00632R,20200612');
        var_dump($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsPriceMultiDays() {
        $this->markTestSkipped();
        $a = new GoodsPriceService();
        $r = $a->getGoodsPrice('2884,20200608,2884,20200609,2884,20200610,2884,20200611,2884,20200612');
        $s = $a->saveGoodPrices($r);
        $this->assertTrue(True);
    }

    public function testGetGoodsPriceMultiDaysETF() {
        $a = new GoodsPriceService();
        $r = $a->getGoodsPrice('0050,20200608,0050,20200609,0050,20200610,0050,20200611,0050,20200612');
        $s = $a->saveGoodPrices($r);
        $this->assertTrue(True);
    }
}
