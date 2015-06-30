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
     * @var int
     */
    protected $timeout;

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
     * @param $timeout
     * @return $this
     */
    public function setBlockTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
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
     * @return null|string
     */
    public function pop()
    {
        $msgList = $this->redis->blpop([$this->name], $this->timeout);
        if ($msgList === null) {
            return null;
        }

        if (extension_loaded('xdebug')) {
            assert(count($msgList) === 2);
            assert($msgList[0] === $this->name);
        }

        $result = $msgList[1];

        if (extension_loaded('xdebug')) {
            assert(is_string($result));
        }

        return $result;
    }
}
