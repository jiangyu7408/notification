<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 5:25 PM
 */

namespace Config;

/**
 * Class ConfigContainer
 * @package ConfigContainer
 */
class ConfigContainer
{
    protected $container = [];

    public function __construct()
    {
        $this->redisConfig = [
            'scheme'  => 'tcp',
            'host'    => '127.0.0.1',
            'port'    => 6379,
            'timeout' => 5.0,
        ];
    }

    /**
     * @return array
     */
    public function getRedisConfig()
    {
        return $this->redisConfig;
    }

    /**
     * @return array
     */
    public function getServiceQueueConfig()
    {
        return array_merge($this->redisConfig, ['queueName' => 'register_service']);
    }
}
