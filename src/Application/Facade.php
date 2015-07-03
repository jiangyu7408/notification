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
use Repository\NotifListRepo;
use Repository\NotifRepo;

/**
 * Class Facade
 * @package Application
 */
class Facade
{
    protected static $instance;

    protected function __construct(Builder $builder)
    {
        $this->builder = $builder;
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
                    'facebook'       => require CONFIG_DIR . '/facebook.php',
                    'facebook_queue' => require CONFIG_DIR . '/facebook_queue.php',
                    'redis'          => require CONFIG_DIR . '/redis.php',
                    'redis_queue'    => require CONFIG_DIR . '/redis_queue.php',
                    'redis_notif'    => require CONFIG_DIR . '/redis_notif.php'
                ]
            );

            static::$instance = new static($builder);
        }

        return static::$instance;
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
    public function getRegisterQueueConfig()
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

    /**
     * @return NotifRepo
     */
    public function getNotifRepo()
    {
        $loaded = $this->container->has(NotifRepo::class);
        if (!$loaded) {
            $this->builder->buildNotif();
        }
        return $this->getService(NotifRepo::class);
    }

    /**
     * @return NotifListRepo
     */
    public function getNotifListRepo()
    {
        $loaded = $this->container->has(NotifListRepo::class);
        if (!$loaded) {
            $this->builder->buildNotifList();
        }
        return $this->getService(NotifListRepo::class);
    }

    /**
     * @return array
     */
    public function getFBGatewayOptions()
    {
        assert($this->container->hasParameter('facebook'));
        assert($this->container->hasParameter('facebook_queue'));

        return array_merge($this->getParam('facebook'), $this->getParam('facebook_queue'));
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
}
