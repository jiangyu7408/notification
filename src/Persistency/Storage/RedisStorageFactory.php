<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 3:32 PM.
 */

namespace Persistency\Storage;

use Config\RedisConfigFactory;
use Config\RedisNotifConfigFactory;

/**
 * Class RedisStorageFactory.
 */
class RedisStorageFactory
{
    /**
     * @param array  $redisOptions
     * @param string $prefix
     *
     * @return RedisStorage
     *
     * @throws \InvalidArgumentException
     */
    public function create(array $redisOptions, $prefix)
    {
        $redisConfig = (new RedisConfigFactory())->create($redisOptions);
        $notifConfig = (new RedisNotifConfigFactory())->create($redisConfig, $prefix);

        $redisClient = RedisClientFactory::create($notifConfig);

        return new RedisStorage($redisClient, $prefix);
    }
}
