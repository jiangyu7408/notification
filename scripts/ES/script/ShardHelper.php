<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:02.
 */
namespace script;

use Environment\Platform;
use PDO;

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
     * @return false|PDO
     */
    public static function pdoFactory(array $options)
    {
        /** @var PDO[] $connections */
        static $connections = [];

        $dsn = 'mysql:dbname='.$options['database'].';host='.$options['host'];
        if (isset($connections[$dsn])) {
            $pdo = $connections[$dsn];
            if (is_bool($pdo)) {
                $connections[$dsn] = $pdo = self::connect($dsn, $options);
            } else {
                $connections[$dsn] = $pdo = self::reconnectIfNeeded($connections[$dsn], $dsn, $options);
            }

            return $pdo;
        }

        $connections[$dsn] = $pdo = self::connect($dsn, $options);

        return $pdo;
    }

    /**
     * @param string $dsn
     * @param array  $options
     *
     * @return false|PDO
     */
    private static function connect($dsn, array $options)
    {
        try {
            appendLog('Connect MySQL on DSN: '.$dsn);
            $pdo = new PDO(
                $dsn,
                $options['username'],
                $options['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 1,
                    PDO::ATTR_CASE => PDO::CASE_NATURAL,
                ]
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (\PDOException $e) {
            appendLog('Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @param PDO    $pdo
     * @param string $dsn
     * @param array  $options
     *
     * @return false|PDO
     */
    private static function reconnectIfNeeded(PDO $pdo, $dsn, array $options)
    {
        try {
            $pdo->query("SHOW STATUS;")->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }

            $pdo = self::connect($dsn, $options);
        }

        return $pdo;
    }
}
