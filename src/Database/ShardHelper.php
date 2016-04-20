<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:02.
 */
namespace Database;

use Environment\PlatformFactory;

/**
 * Class ShardHelper.
 */
class ShardHelper
{
    /**
     * @param string $gameVersion
     *
     * @return \Generator $option1, $option2, ...
     */
    public static function shardConfigGenerator($gameVersion)
    {
        $optionsGenerator = PlatformFactory::make($gameVersion)->getMySQLShards();
        foreach ($optionsGenerator as $options) {
            yield $options;
        }
    }

    /**
     * @param string $gameVersion
     *
     * @return array ['db1' => [], 'db2' => [], ...]
     */
    public static function listShardOptions($gameVersion)
    {
        $optionsGenerator = PlatformFactory::make($gameVersion)->getMySQLShards();

        $configArray = [];
        foreach ($optionsGenerator as $options) {
            $shardId = $options['shardId'];
            $configArray[$shardId] = $options;
        }

        return $configArray;
    }

    /**
     * @param string $gameVersion
     *
     * @return array ['db1', 'db2', ...]
     */
    public static function listShardId($gameVersion)
    {
        $optionsGenerator = PlatformFactory::make($gameVersion)->getMySQLShards();

        $shardIdList = [];
        foreach ($optionsGenerator as $options) {
            $shardIdList[] = $options['shardId'];
        }

        return $shardIdList;
    }
}
