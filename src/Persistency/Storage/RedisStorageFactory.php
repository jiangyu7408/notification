<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 3:32 PM
 */

namespace Persistency\Storage;

/**
 * Class RedisStorageFactory
 * @package Persistency\Storage
 */
class RedisStorageFactory
{
    /**
     * @param array $config
     * @return RedisStorage
     */
    public function create(array $config = [])
    {
        $redisClient = RedisClientFactory::create($this->buildConfig($config));

        return new RedisStorage($redisClient, 'notif');
    }

    /**
     * @param array $config
     * @return array
     */
    public function buildConfig(array $config)
    {
        $defaultConfig = [
            'scheme'  => 'tcp',
            'host'    => '127.0.0.1',
            'port'    => 6379,
            'timeout' => 5.0,
        ];

        return array_replace($defaultConfig, $config);
    }
}
