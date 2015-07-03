<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 3:32 PM
 */

namespace Persistency\Storage;

use Config\RedisConfig;

/**
 * Class RedisStorageFactory
 * @package Persistency\Storage
 */
class RedisStorageFactory
{
    /**
     * @param RedisConfig $config
     * @return RedisStorage
     */
    public function create(RedisConfig $config)
    {
        $redisClient = RedisClientFactory::create($config);

        return new RedisStorage($redisClient, 'notif');
    }
}
