<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\RemoteUrlService;

class GetUrlTest extends TestCase
{
    
    public function testGetUrl() {
        $this->markTestSkipped();
        $a = new RemoteUrlService();
        $url = 'http://cloud.usagi.tw/~usagi/usagilab/index.php/device_uptime/status/usagi/';
        $r = $a->getUrl($url);
        echo($r);
        $this->assertTrue(True);
    }
    

}
