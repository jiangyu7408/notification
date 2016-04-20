<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/05
 * Time: 11:23.
 */
namespace DataProvider\User;

use Database\PdoFactory;
use Database\ShardHelper;

/**
 * Class InstallGenerator.
 */
class InstallGenerator
{
    /**
     * @param string $gameVersion
     * @param string $date
     * @param bool   $verbose
     *
     * @return array
     */
    public static function generate($gameVersion, $date, $verbose)
    {
        $shardConfigList = ShardHelper::shardConfigGenerator($gameVersion);

        $groupedUidList = [];
        foreach ($shardConfigList as $shardConfig) {
            $shardId = $shardConfig['shardId'];
            $groupedUidList[$shardId] = self::onShard($gameVersion, $shardId, $date, $verbose);
        }

        return $groupedUidList;
    }

    /**
     * @param string $gameVersion
     * @param string $shardId
     * @param string $date
     * @param bool   $verbose
     *
     * @return array [uid, uid]
     */
    protected static function onShard($gameVersion, $shardId, $date, $verbose = false)
    {
        assert(is_string($date) && strlen($date) == strlen('2016-04-04'));
        $pdo = PdoFactory::makePool($gameVersion)->getByShardId($shardId);
        if ($pdo === false) {
            return [];
        }

        \PHP_Timer::start();
        $uidList = self::fetchNewUser($pdo, $date);
        $timeCost = \PHP_Timer::secondsToTimeString(\PHP_Timer::stop());

        if ($verbose) {
            appendLog(
                sprintf(
                    '%s %s cost %s to get %d uid',
                    __CLASS__,
                    $shardId,
                    $timeCost,
                    count($uidList)
                )
            );
        }

        return $uidList;
    }

    /**
     * @param \PDO   $pdo
     * @param string $date
     *
     * @return array [uid, uid]
     */
    protected static function fetchNewUser(\PDO $pdo, $date)
    {
        $query = 'select uid from tbl_user where date(addtime)=?';
        $statement = $pdo->prepare($query);
        $statement->execute([$date]);

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
