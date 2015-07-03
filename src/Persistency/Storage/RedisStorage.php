<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 12:14 PM
 */

namespace Persistency\Storage;

use Predis\Client;

/**
 * Class RedisStorage
 * @package Persistency\Storage
 */
class RedisStorage
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param Client $client
     * @param string $prefix
     */
    public function __construct(Client $client, $prefix)
    {
        assert(is_string($prefix));
        $this->client = $client;
        $this->prefix = $prefix;
    }

    /**
     * @param array $payload
     * @return int
     */
    public function add(array $payload)
    {
        if (!isset($payload['fireTime'])) {
            trigger_error('payload should has key: fireTime, and >0');
        }
        $fireTime = $payload['fireTime'];

        $key   = $this->makeKey($fireTime);
        $value = json_encode($payload);
        $field = md5($value);

        $ret = $this->client->hset($key, $field, $value);
//        var_dump(__METHOD__ . ": key[$key] field[$field] value[$value] hset return " . var_export($ret, true));

        return $ret;
    }

    /**
     * @param int $fireTime
     * @return string
     */
    private function makeKey($fireTime)
    {
        return $this->prefix . '_' . $fireTime;
    }

    /**
     * @param int $fireTime
     * @return array
     */
    public function getList($fireTime)
    {
        $list = $this->client->hgetall($this->makeKey($fireTime));

        return array_map(function ($value) {
            return json_decode($value, true);
        }, $list);
    }

    /**
     * @param int $fireTime
     * @return bool
     */
    public function purgeList($fireTime)
    {
        return (bool)$this->client->del([$this->makeKey($fireTime)]);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
