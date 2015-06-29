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
     * @return RedisStorage
     */
    public function create()
    {
        $redis = RedisFactory::create([
            'scheme'  => 'tcp',
            'host'    => '10.0.64.56',
            'port'    => 6379,
            'timeout' => 5.0,
        ]);

        return new RedisStorage($redis, 'notif');
    }
}
