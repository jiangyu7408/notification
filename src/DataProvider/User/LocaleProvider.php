<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 16:04.
 */
namespace DataProvider\User;

use Database\PdoPool;
use Facade\ShardIdMarker;

/**
 * Class LocaleProvider.
 */
class LocaleProvider
{
    /** @var string */
    protected $gameVersion;
    /** @var PdoPool */
    protected $pdoPool;
    /** @var array */
    protected $deltaList = [];
    /** @var ShardIdMarker */
    protected $marker;

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
     * @param int      $batch
     */
    public function walk(\Closure $callback, $batch = 500)
    {
        $shardIdList = $this->pdoPool->listShardId();
        foreach ($shardIdList as $shardId) {
            if ($this->marker->isMarked($shardId)) {
                continue;
            }
            $this->onShard($shardId, $callback, $batch);
            $this->marker->mark($shardId);
        }
    }

    /**
     * @param ShardIdMarker $marker
     */
    public function setMarker(ShardIdMarker $marker)
    {
        $this->marker = $marker;
    }

    /**
     * @param string   $shardId
     * @param \Closure $callback
     * @param int      $batch
     */
    protected function onShard($shardId, \Closure $callback, $batch)
    {
        $pdo = $this->pdoPool->getByShardId($shardId);
        if ($pdo === false) {
            return;
        }

        $sql = 'SELECT uid,locale FROM tbl_user_locale';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $resultSet = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        while (($dataSet = array_splice($resultSet, 0, $batch))) {
            $pairs = [];
            array_map(
                function (array $user) use (&$pairs) {
                    $pairs[$user['uid']] = $user['locale'];
                },
                $dataSet
            );

            $userInfoList = CommonInfoProvider::readUserInfo($pdo, array_keys($pairs), 100, 'uid,snsid');

            $payload = [];
            foreach ($userInfoList as $uid => $user) {
                $snsid = $user['snsid'];
                $payload[$uid] = ['snsid' => $snsid, 'locale' => $pairs[$uid]];
            }

            call_user_func($callback, $shardId, $payload);
        }
    }
}
