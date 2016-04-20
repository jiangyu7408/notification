<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 11:29.
 */
namespace Database;

use Environment\PlatformFactory;

/**
 * Class PdoFactory.
 */
class PdoFactory extends PdoPool
{
    /** @var PdoPool */
    protected static $instance;

    /**
     * @param array $shardOptions
     *
     * @return PdoPool
     */
    public static function make(array $shardOptions)
    {
        if (self::$instance === null) {
            self::$instance = new PdoPool($shardOptions);
        }

        return self::$instance;
    }

    /**
     * @param string $gameVersion
     *
     * @return PdoPool
     */
    public static function makePool($gameVersion)
    {
        if (self::$instance === null) {
            $shardOptions = ShardHelper::listShardOptions($gameVersion);
            self::$instance = new PdoPool($shardOptions);
        }

        return self::$instance;
    }

    /**
     * @param string $gameVersion
     *
     * @return false|\PDO
     */
    public static function makeGlobalPdo($gameVersion)
    {
        $pdoPool = self::makePool($gameVersion);
        $shardId = PlatformFactory::make($gameVersion)->locateIdMap();

        return $pdoPool->getByShardId($shardId);
    }
}
