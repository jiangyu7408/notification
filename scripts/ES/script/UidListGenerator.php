<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:04.
 */
namespace script;

/**
 * Class UidListGenerator.
 */
class UidListGenerator
{
    /**
     * @param string $gameVersion
     * @param int    $fromTs
     * @param bool   $verbose
     *
     * @return array
     */
    public static function generate($gameVersion, $fromTs, $verbose)
    {
        $shardConfigList = ShardHelper::shardConfigGenerator($gameVersion);

        $groupedUidList = [];
        foreach ($shardConfigList as $shardConfig) {
            $shardId = $shardConfig['shardId'];
            $groupedUidList[$shardId] = self::onShard($shardConfig, $fromTs, $verbose);
        }

        return $groupedUidList;
    }

    /**
     * @param array $mysqlOptions
     * @param int   $lastActiveTimestamp
     * @param bool  $verbose
     *
     * @return array [uid, uid]
     */
    protected static function onShard(array $mysqlOptions, $lastActiveTimestamp, $verbose = false)
    {
        $pdo = ShardHelper::pdoFactory($mysqlOptions);
        if ($pdo === false) {
            return [];
        }
        $shardId = $mysqlOptions['shardId'];

        \PHP_Timer::start();
        $uidList = self::fetchActiveUidList($pdo, $lastActiveTimestamp);
        $timeCost = \PHP_Timer::secondsToTimeString(\PHP_Timer::stop());

        if ($verbose) {
            appendLog(sprintf('fetchActiveUidList %s cost %s to get %d uid', $shardId, $timeCost, count($uidList)));
        }

        return $uidList;
    }

    /**
     * @param \PDO $pdo
     * @param int  $lastActiveTimestamp
     *
     * @return array [uid, uid]
     */
    protected static function fetchActiveUidList(\PDO $pdo, $lastActiveTimestamp)
    {
        $query = 'select uid from tbl_user_session force index (time_last_active) where time_last_active>?';
        $statement = $pdo->prepare($query);
        $statement->execute([(int) $lastActiveTimestamp]);

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
