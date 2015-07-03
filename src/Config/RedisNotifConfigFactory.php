<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 2:06 PM
 */

namespace Config;

/**
 * Class RedisNotifConfigFactory
 * @package Config
 */
class RedisNotifConfigFactory
{
    /**
     * @param RedisConfig $config
     * @param $prefix
     * @return RedisNotifConfig
     */
    public function create(RedisConfig $config, $prefix)
    {
        $options = array_merge($config->toArray(), ['prefix' => $prefix]);

        $configObject = new RedisNotifConfig();
        foreach ($options as $key => $value) {
            $configObject->$key = $value;
        }

        return $configObject;
    }
}
