<?php
/**
 * 外部存取相關
 */
namespace App\Services;

use GuzzleHttp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class RemoteUrlService {
    
    private $expire_seconds = 3;
    private $obj_redis;

    
    public function __construct() {
//        $this->obj_redis = new Redis();
//        $this->obj_redis->connect(env('REDIS_HOST'));
        $this->obj_redis = Redis::connection();
    }
    
    /**
     * 取得URL內容並回傳，如果有cache就從cache撈
     * @param string $url
     * @param integer $ttl
     */
    public function getUrl(string $url, int $ttl = 30) {
        Log::info($url . PHP_EOL);
        $cache_key = 'URL_' . md5($url);
        // 先看cache有沒有
        $response = Cache::get($cache_key, false);
        if ($response == false) {
            Log::info('cache miss');
            // 沒有，打URL
            $response = $this->waitForGetUrl($url);
            Cache::put($cache_key, $response, $ttl);
        } else {
            Log::info('cache hit');
        }
        /*
        $response = Cache::remember($cache_key, $ttl, function() use ($url) {
            return $this->waitForGetUrl($url);
        });
        */
        return $response;
    }
    
    /**
     * 避免一直打URL被擋
     * @param unknown $url
     * @return string
     */
    private function waitForGetUrl($url) {
//        Log::info(__FUNCTION__ . PHP_EOL);
        while ($this->getExpireFlag() <> false) {
            Log::info('sleeping' . PHP_EOL);
            sleep($this->expire_seconds);
        }
        $this->setExpireFlag();
        try {
            $http_client = new GuzzleHttp\Client();
            $res = $http_client->request('GET', $url);
//            $obj_http = Http::get($url);
        } catch (Exception $e) {
            Log::error('CURL連線異常');
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            Log::error($res->getStatusCode());
            return false;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Guzzle連線異常');
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            return false;
        }
        return $res->getBody();
//        return $obj_http->body();
    }
    
    /**
     * 設定過期旗標
     */
    private function setExpireFlag() {
//        Log::info(__FUNCTION__ . PHP_EOL);
        $this->obj_redis->set('get_url_expire', 'flag');
        $this->obj_redis->expire('get_url_expire', $this->expire_seconds);
    }
    
    private function getExpireFlag() {
//        Log::info(__FUNCTION__ . PHP_EOL);
        return $this->obj_redis->get('get_url_expire');
    }
    
}

