<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 5:45 PM
 */

namespace Config;

/**
 * Class RedisConfigFactory
 * @package ConfigContainer
 */
class RedisConfigFactory
{
    /**
     * @param array $config
     * @return RedisConfig
     * @throws \InvalidArgumentException
     */
    public function create(array $config)
    {
        return $this->setParam(new RedisConfig(), $config);
    }

    /**
     * @param RedisConfig $redisConfig
     * @param array $config
     * @return RedisConfig
     * @throws \InvalidArgumentException
     */
    protected function setParam(RedisConfig $redisConfig, array $config)
    {
        $keys = array_keys(get_object_vars($redisConfig));
        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new \InvalidArgumentException('bad config: check key => ' . $key);
            }
            $redisConfig->$key = $config[$key];
        }

        return $redisConfig;
    }
}
