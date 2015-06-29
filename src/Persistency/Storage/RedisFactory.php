<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 11:51 AM
 */

namespace Persistency\Storage;

use Predis\Client;
use Predis\Connection\Parameters;

/**
 * Class RedisFactory
 * @package Persistency\Storage
 */
class RedisFactory
{
    protected static $instances = [];

    /**
     * @param array $params
     * @return \Predis\Client
     */
    public static function create(array $params)
    {
        $key = md5(json_encode($params));
        if (!array_key_exists($key, self::$instances)) {

            $defaultParams = [
                'scheme'  => 'tcp',
                'host'    => '10.0.64.56',
                'port'    => 6379,
                'timeout' => 5.0,
            ];

            if (count($params) === 0) {
                $params = $defaultParams;
            }

            self::$instances[$key] = new Client(new Parameters($params));
        }

        return self::$instances[$key];
    }
}
