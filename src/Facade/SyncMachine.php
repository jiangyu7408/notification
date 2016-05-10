<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/01
 * Time: 16:43.
 */
namespace Facade;

use Buffer\AggregatorPersist;
use Buffer\UidAggregator;
use Buffer\UidQueue;
use Database\PdoFactory;
use Database\ShardHelper;
use DataProvider\User\UserDetailProvider;
use Facade\ES\IndexerFactory;
use Facade\ES\Manager;

/**
 * Class SyncMachine.
 */
class SyncMachine
{
    const FLUSH_MAGIC_NUMBER = 500;
    /** @var string */
    protected $gameVersion;
    /** @var UidAggregator */
    protected $aggregator;
    /** @var array */
    protected $shardList;
    /** @var UserDetailProvider */
    protected $dataProvider;
    /** @var ES\Indexer */
    protected $indexer;

    /**
     * SyncMachine constructor.
     *
     * @param string $gameVersion
     * @param string $esHost
     */
    public function __construct($gameVersion, $esHost)
    {
        $this->gameVersion = $gameVersion;
        $this->esHost = $esHost;
        $date = date('Ymd');
        $persist = new AggregatorPersist(LOG_DIR.'/'.$gameVersion.'.uid.persist');
        $this->aggregator = new UidAggregator($persist);
        $this->shardList = ShardHelper::listShardId($gameVersion);
        $this->logFile = LOG_DIR.'/'.$date.'/'.$gameVersion.'.machine';
        $this->prepareLogDir($this->logFile);

        $this->dataProvider = new UserDetailProvider($gameVersion, PdoFactory::makePool($gameVersion));
        $this->indexer = IndexerFactory::make($esHost, $gameVersion, self::FLUSH_MAGIC_NUMBER);
    }

    /**
     * @param int $repeatTimes
     * @param int $waitTime
     */
    public function run($repeatTimes, $waitTime)
    {
        $queue = new UidQueue(UID_QUEUE_DIR, $this->gameVersion, $this->shardList);
        $groupedUidList = $queue->pop();
        array_walk($groupedUidList, function (array $uidList, $shardId) {
            if (count($uidList) === 0) {
                return;
            }
            appendLog(__CLASS__.': [before aggregate] '.$shardId.' have uid '.count($uidList));
        });
        $afterAggregate = $this->aggregate($groupedUidList, $repeatTimes, $waitTime);
        $now = date('Y-m-d H:i:s');
        array_walk($afterAggregate, function (array $uidList, $shardId) use ($now) {
            if (count($uidList) === 0) {
                return;
            }
            appendLog(__CLASS__.': [after aggregate] '.$shardId.' have uid '.count($uidList));
            array_map(
                function ($uid) use ($now) {
                    \error_log(sprintf('%d => %s'.PHP_EOL, $uid, $now), 3, $this->logFile);
                },
                $uidList
            );
        });

        (new Manager($this->dataProvider, $this->indexer))->updateES($afterAggregate);
    }

    /**
     * @param array $groupedUidList
     * @param int   $repeatTimes
     * @param int   $waitTime
     *
     * @return array
     */
    private function aggregate(array $groupedUidList, $repeatTimes, $waitTime)
    {
        $now = time();
        $beforeTs = $now - $waitTime;
        appendLog(
            sprintf(
                '%s: on time %s(%d) with repeat>=%d or waitTime>=%d(%s)',
                __METHOD__,
                date('c', $now),
                $now,
                $repeatTimes,
                $waitTime,
                date('c', $beforeTs)
            )
        );
        array_walk(
            $groupedUidList,
            function (array $uidList, $shardId) use ($now) {
                $this->aggregator->add($shardId, $uidList, $now);
            }
        );

        $rawData = $this->aggregator->filter($repeatTimes, $beforeTs);

        $groupedUidList = [];
        array_walk(
            $rawData,
            function (array $uidList, $shardId) use (&$groupedUidList) {
                $groupedUidList[$shardId] = array_keys($uidList);
            }
        );

        return $groupedUidList;
    }

    /**
     * @param string $logFile
     */
    private function prepareLogDir($logFile)
    {
        assert(strpos($logFile, '/mnt/htdocs/notif') === 0);
        $dirName = dirname($logFile);
        if (!is_dir($dirName)) {
            $success = mkdir($dirName, 0755, true);
            assert($success);
        }
    }
}
