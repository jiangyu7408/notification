<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 11:33.
 */
namespace Environment;

/**
 * Class PlatformFactory.
 */
class PlatformFactory extends Platform
{
    /** @var Platform[] */
    protected static $instances = [];

    /**
     * @param string $gameVersion
     *
     * @return Platform
     */
    public static function make($gameVersion)
    {
        if (!isset(self::$instances[$gameVersion])) {
            $base = CONFIG_DIR.'/../farm-server-conf/';
            assert(is_dir($base));
            self::$instances[$gameVersion] = new Platform($base, $gameVersion);
        }

        return self::$instances[$gameVersion];
    }
}
