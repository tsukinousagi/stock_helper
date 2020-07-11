<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\SubscriptionService;
use App\Enums\GoodsTraceType;

class SubscriptionTest extends TestCase
{
    public function testToggleSubscription()
    {
        $obj_sub = new SubscriptionService();
        $ret = $obj_sub->toggleSubscription('123', '2633', GoodsTraceType::Turning());
        $this->assertTrue(true);
    }

    public function testGetSubscriptions()
    {
        $obj_sub = new SubscriptionService();
        $ret = $obj_sub->getSubscriptionsByChatId('123');
        var_dump($ret);
        $this->assertTrue(true);
    }
}
