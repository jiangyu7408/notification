<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 5:50 PM
 */

namespace Config;

/**
 * Class RedisQueueConfigFactory
 * @package ConfigContainer
 */
class RedisQueueConfigFactory extends RedisConfigFactory
{
    /**
     * @param array $config
     * @return RedisConfig
     * @throws \InvalidArgumentException
     */
    public function create(array $config)
    {
        return $this->setParam(new RedisQueueConfig(), $config);
    }

    /**
     * @param RedisQueueConfig $config
     * @return array
     */
    public function toArray(RedisQueueConfig $config)
    {
        return get_object_vars($config);
    }
}
