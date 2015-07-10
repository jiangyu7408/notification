<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 11:51 AM.
 */

namespace Persistency\Storage;

use Config\RedisConfig;
use Predis\Client;
use Predis\Connection\Parameters;

/**
 * Class RedisClientFactory.
 */
class RedisClientFactory
{
    protected static $instances = [];

    /**
     * @param RedisConfig $config
     *
     * @return Client
     */
    public static function create(RedisConfig $config)
    {
        $hash = $config->hash();
        if (!array_key_exists($hash, self::$instances)) {
            self::$instances[$hash] = new Client(new Parameters($config->toArray()));
        }

        return self::$instances[$hash];
    }
}
