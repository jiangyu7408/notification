<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 14:25.
 */
namespace DataProvider\User;

use Database\PdoPool;

/**
 * Class UninstallUidProvider.
 */
class UninstallUidProvider
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
     * @param \Closure $callback
     *
     * @return array ['db1' => [uid, uid, 'db2' => [uid, uid]]
     */
    public function generate(\Closure $callback = null)
    {
        $groupedUidList = [];

        $shardIdList = $this->pdoPool->listShardId();
        array_map(
            function ($shardId) use (&$groupedUidList, $callback) {
                $start = microtime(true);

                $uidList = $this->onShard($shardId);
                $groupedUidList[$shardId] = $uidList;

                if (is_callable($callback)) {
                    $delta = microtime(true) - $start;
                    call_user_func($callback, $shardId, count($uidList), $delta);
                }
            },
            $shardIdList
        );

        return $groupedUidList;
    }

    /**
     * @param string $shardId
     *
     * @return array [uid, uid]
     */
    protected function onShard($shardId)
    {
        $pdo = $this->pdoPool->getByShardId($shardId);
        if ($pdo === false) {
            return [];
        }

        $uidList = $this->queryMysql($pdo);

        return $uidList;
    }

    /**
     * @param \PDO $pdo
     *
     * @return array [uid, uid]
     */
    protected function queryMysql(\PDO $pdo)
    {
        $sql = 'SELECT uid FROM tbl_user_remove_log';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
