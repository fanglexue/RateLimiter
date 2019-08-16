<?php
/*-----------------------------------------------------------
* Filename      : RateLimiter.php
* Author        : fanglexue - 15216647880@163.com
* Description   : 借用redis 实现分布式 平滑限流 
* Create        : 2019-08-16 13:55:23
* Last Modified : 2019-08-16 13:55:23
*-----------------------------------------------------------*/

/**
 * 限流
 * Class RateLimiter
 * @package app\api\model
 */

class RateLimiter {

    private $redis = null;
    
    public  function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param $allowedRequests
     * @param $seconds
     * @return bool
     */
    public function limitRequestsInSeconds($allowedRequests, $seconds) {
        $requests = 0;
        $keys = $this->getKeys($seconds);

        foreach ($keys as $key) {
            $requestsInCurrentSecond= $this->redis->get($key);
            if (false !== $requestsInCurrentSecond) $requests += intval($requestsInCurrentSecond) + 1;
        }

        if (false === $requestsInCurrentSecond) {
            $this->redis->set($key, 1, $seconds + 1);
        } else {
            $this->redis->Incr($key, 1);
        }
        if ($requests > $allowedRequests) return false;
        return true;
    }

    /**
     * @param $seconds
     * @return array
     */
    private function getKeys($seconds) {
        $keys = array();
        $now = time();
        for ($time = $now - $seconds; $time <= $now; $time++) {
            $keys[] = date("dHis", $time);
        }
        return $keys;
    }
}
