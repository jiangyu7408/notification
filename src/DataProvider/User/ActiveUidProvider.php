<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:04.
 */
namespace DataProvider\User;

use Database\PdoPool;

/**
 * Class ActiveUidProvider.
 */
class ActiveUidProvider
{
    /** @var string */
    protected $gameVersion;
    /** @var PdoPool */
    protected $pdoPool;
    /** @var array */
    protected $deltaList = [];

    /**
     * UserDetailProvider constructor.
     *
     * @param string  $gameVersion
     * @param PdoPool $pdoPool
     */
    public function __construct($gameVersion, PdoPool $pdoPool)
    {
        $this->gameVersion = $gameVersion;
        $this->pdoPool = $pdoPool;
    }

    /**
     * @param int $fromTs
     *
     * @return array ['db1' => [uid, uid, 'db2' => [uid, uid]]
     */
    public function generate($fromTs)
    {
        $this->deltaList = [];
        $groupedUidList = [];
        array_map(
            function ($shardId) use (&$groupedUidList, $fromTs) {
                $groupedUidList[$shardId] = $this->onShard($shardId, $fromTs);
            },
            $this->pdoPool->listShardId()
        );

        return $groupedUidList;
    }

    /**
     * @return float[] ['db1' => float, 'db2' => float]
     */
    public function getDeltaList()
    {
        return $this->deltaList;
    }

    /**
     * @param string $shardId
     * @param int    $fromTs
     *
     * @return array [uid, uid]
     */
    protected function onShard($shardId, $fromTs)
    {
        $pdo = $this->pdoPool->getByShardId($shardId);
        if ($pdo === false) {
            return [];
        }

        $start = microtime(true);
        $uidList = $this->fetchActiveUser($pdo, $fromTs);
        $delta = microtime(true) - $start;

        $this->deltaList[$shardId] = $delta;

        return $uidList;
    }

    /**
     * @param \PDO $pdo
     * @param int  $fromTs
     *
     * @return array [uid, uid]
     */
    protected function fetchActiveUser(\PDO $pdo, $fromTs)
    {
        $query = 'select uid from tbl_user_session force index (time_last_active) where time_last_active>?';
        $statement = $pdo->prepare($query);
        $statement->execute([(int) $fromTs]);

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
