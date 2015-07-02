<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 8:18 PM
 */

namespace Application;

use Config\RedisConfig;
use Config\RedisConfigFactory;
use Config\RedisQueueConfig;
use Config\RedisQueueConfigFactory;

/**
 * Class Facade
 * @package Application
 */
class Facade
{
    protected static $instance;

    protected function __construct(Builder $builder)
    {
        $this->container = $builder->create();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            $builder = new Builder(
                [
                    'redis'      => require CONFIG_DIR . '/redis.php',
                    'redisQueue' => require CONFIG_DIR . '/redis_queue.php'
                ]
            );

            static::$instance = new static($builder);
        }

        return static::$instance;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        try {
            return $this->container->getParameter($name);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return RedisConfig|null
     */
    public function getRedisConfig()
    {
        return $this->getService(RedisConfig::class);
    }

    /**
     * @param string $name
     * @return null|object
     */
    public function getService($name)
    {
        try {
            return $this->container->get($name);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return null|RedisConfigFactory
     */
    public function getRedisConfigFactory()
    {
        return $this->getService(RedisConfigFactory::class);
    }

    /**
     * @return null|RedisQueueConfig
     */
    public function getRedisQueueConfig()
    {
        return $this->getService(RedisQueueConfig::class);
    }

    /**
     * @return null|RedisQueueConfigFactory
     */
    public function getRedisQueueConfigFactory()
    {
        return $this->getService(RedisQueueConfigFactory::class);
    }
}
