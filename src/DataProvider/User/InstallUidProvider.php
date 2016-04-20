<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/05
 * Time: 11:23.
 */
namespace DataProvider\User;

use Database\PdoPool;
use Database\ShardHelper;

/**
 * Class InstallUidProvider.
 */
class InstallUidProvider
{
    /** @var string */
    protected $gameVersion;
    /** @var PdoPool */
    protected $pdoPool;
    /** @var array */
    protected $deltaList = [];

    /**
     * InstallUidProvider constructor.
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
     * @param string $date
     *
     * @return array ['db1' => [uid, uid, 'db2' => [uid, uid]]
     */
    public function generate($date)
    {
        if (!(is_string($date) && strlen($date) == strlen('2016-04-04'))) {
            throw new \InvalidArgumentException('date format should be like 2016-04-04');
        }

        $this->deltaList = [];
        $groupedUidList = [];
        array_map(
            function ($shardId) use (&$groupedUidList, $date) {
                $groupedUidList[$shardId] = $this->onShard($shardId, $date);
            },
            ShardHelper::listShardId($this->gameVersion)
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
     * @param string $date
     *
     * @return array [uid, uid]
     */
    protected function onShard($shardId, $date)
    {
        $pdo = $this->pdoPool->getByShardId($shardId);
        if ($pdo === false) {
            return [];
        }

        $start = microtime(true);
        $uidList = $this->fetchNewUser($pdo, $date);
        $delta = microtime(true) - $start;

        $this->deltaList[$shardId] = $delta;

        return $uidList;
    }

    /**
     * @param \PDO   $pdo
     * @param string $date
     *
     * @return array [uid, uid]
     */
    protected function fetchNewUser(\PDO $pdo, $date)
    {
        $sql = 'SELECT uid FROM tbl_user WHERE DATE(addtime)=?';
        $statement = $pdo->prepare($sql);
        $statement->execute([$date]);

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
