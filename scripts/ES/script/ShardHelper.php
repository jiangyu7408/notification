<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:02.
 */
namespace script;

use Environment\Platform;

/**
 * Class ShardHelper.
 */
class ShardHelper
{
    /**
     * @param string $gameVersion
     *
     * @return \Generator
     */
    public static function shardConfigGenerator($gameVersion)
    {
        $shardConfigList = self::platformFactory()->getMySQLShards($gameVersion);
        foreach ($shardConfigList as $shardConfig) {
            assert(is_array($shardConfig));
            yield $shardConfig;
        }
    }

    /**
     * @param string $gameVersion
     *
     * @return array
     */
    public static function getShardList($gameVersion)
    {
        $shardConfigList = self::platformFactory()->getMySQLShards($gameVersion);

        $shardIdList = [];
        foreach ($shardConfigList as $shardConfig) {
            assert(is_array($shardConfig));
            $shardIdList[] = $shardConfig['shardId'];
        }

        return $shardIdList;
    }

    /**
     * @return Platform
     */
    public static function platformFactory()
    {
        $base = CONFIG_DIR.'/../farm-server-conf/';
        assert(is_dir($base));
        $platform = new Platform($base);

        return $platform;
    }

    /**
     * @param array $options
     *
     * @return false|\PDO
     */
    public static function pdoFactory(array $options)
    {
        static $connections = [];

        $dsn = 'mysql:dbname='.$options['database'].';host='.$options['host'];
        if (isset($connections[$dsn])) {
            return $connections[$dsn];
        }

        try {
            appendLog('Connect MySQL on DSN: '.$dsn);
            $connections[$dsn] = $pdo = new \PDO($dsn, $options['username'], $options['password']);

            return $pdo;
        } catch (\PDOException $e) {
            appendLog('Error: '.$e->getMessage());

            return false;
        }
    }
}
