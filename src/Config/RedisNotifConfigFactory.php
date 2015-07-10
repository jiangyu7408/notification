<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 2:06 PM.
 */

namespace Config;

/**
 * Class RedisNotifConfigFactory.
 */
class RedisNotifConfigFactory
{
    /**
     * @param RedisConfig $config
     * @param string      $prefix
     *
     * @return RedisNotifConfig
     */
    public function create(RedisConfig $config, $prefix)
    {
        if (!is_string($prefix)) {
            trigger_error('prefix should be a string', E_USER_ERROR);
        }
        $options = array_merge($config->toArray(), ['prefix' => $prefix]);

        $configObject = new RedisNotifConfig();
        foreach ($options as $key => $value) {
            $configObject->$key = $value;
        }

        return $configObject;
    }
}
