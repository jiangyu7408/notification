<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:04.
 */
namespace DataProvider\User;

use Database\PdoFactory;
use Database\ShardHelper;

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
        $shardIdList = ShardHelper::listShardId($gameVersion);

        $groupedUidList = [];
        foreach ($shardIdList as $shardId) {
            $groupedUidList[$shardId] = self::onShard($gameVersion, $shardIdList, $fromTs, $verbose);
        }

        return $groupedUidList;
    }

    /**
     * @param string $gameVersion
     * @param string $shardId
     * @param int    $lastActiveTimestamp
     * @param bool   $verbose
     *
     * @return array [uid, uid]
     */
    protected static function onShard($gameVersion, $shardId, $lastActiveTimestamp, $verbose = false)
    {
        $pdo = PdoFactory::makePool($gameVersion)->getByShardId($shardId);
        if ($pdo === false) {
            return [];
        }

        \PHP_Timer::start();
        $uidList = self::fetchActiveUidList($pdo, $lastActiveTimestamp);
        $timeCost = \PHP_Timer::secondsToTimeString(\PHP_Timer::stop());

        if ($verbose) {
            appendLog(
                sprintf(
                    'fetchActiveUidList %s cost %s to get %d uid',
                    $shardId,
                    $timeCost,
                    count($uidList)
                )
            );
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
