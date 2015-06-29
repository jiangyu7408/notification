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
    protected $name;

    /**
     * @param Client $client
     * @param string $name
     */
    public function __construct(Client $client, $name)
    {
        $this->client = $client;
        $this->name   = $name;
    }

    /**
     * @param array $payload
     */
    public function add(array $payload)
    {
        if (!isset($payload['fireTime'])) {
            trigger_error('payload should has key: fireTime, and >0');
        }
        $fireTime = $payload['fireTime'];
        unset($payload['fireTime']);
        $this->client->hset($this->name, $fireTime, json_encode($payload));
    }

    /**
     * @param int $fireTime
     * @return array
     */
    public function getList($fireTime)
    {
        $list = $this->client->hgetall($this->name, $fireTime);

        $result = [];
        foreach ($list as $each) {
            $result[] = json_decode($each, true);
        }
        return $result;
    }

    public function purgeList($fireTime)
    {
        $this->client->del([$fireTime]);
    }
}
