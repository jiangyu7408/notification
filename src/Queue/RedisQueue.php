<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 11:46 AM
 */

namespace Queue;

use Predis\Client;

class RedisQueue implements IQueue
{
    /**
     * @param Client $client
     * @param string $name
     */
    public function __construct(Client $client, $name = 'queue')
    {
        $this->redis = $client;
        $this->name  = $name;
    }

    /**
     * @param string $msg
     * @return int
     */
    public function push($msg)
    {
        $length = $this->redis->rpush($this->name, [$msg]);
        return $length;
    }

    /**
     * @return string
     */
    public function pop()
    {
        $msg = $this->redis->lpop($this->name);
        return $msg;
    }
}
