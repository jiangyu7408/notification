<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/01
 * Time: 16:43.
 */
namespace script;

use Application\ESGatewayBuilder;
use ESGateway\Factory;
use ESGateway\User;
use PHP_Timer;
use Repository\ESGatewayUserRepo;

/**
 * Class Machine.
 */
class Machine
{
    /** @var string */
    protected $gameVersion;
    /** @var UidAggregator */
    protected $aggregator;
    /** @var array */
    protected $shardList;

    /**
     * Machine constructor.
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
        $this->shardList = ShardHelper::getShardList($gameVersion);
        $this->logFile = LOG_DIR.'/'.$date.'/'.$gameVersion.'.machine';
        $this->prepareLogDir($this->logFile);
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

        $this->updateES($afterAggregate);
    }

    /**
     * @param array $groupedUidList
     */
    private function updateES(array $groupedUidList)
    {
        $count = $this->countUidList($groupedUidList);
        if ($count === 0) {
            appendLog(sprintf('%s: have 0 user to sync', __METHOD__));

            return;
        }

        PHP_Timer::start();
        $groupedUserList = UserDetailGenerator::generate($this->gameVersion, $groupedUidList);
        $delta = PHP_Timer::stop();
        appendLog(
            sprintf(
                'Fetch %d user detail cost %s with average cost %s',
                $count,
                PHP_Timer::secondsToTimeString($delta),
                PHP_Timer::secondsToTimeString($delta / $count)
            )
        );

        $flatUserList = [];
        foreach ($groupedUserList as $shardId => $userList) {
            $count = count($userList);
            if ($count === 0) {
                continue;
            }
            appendLog(sprintf('%s: %s have %d user to sync', __METHOD__, $shardId, $count));
            $flatUserList = array_merge($flatUserList, $userList);
        }
        $this->batchUpdateES($this->esHost, $this->gameVersion, $flatUserList);
    }

    /**
     * @param string $esHost
     * @param string $gameVersion
     * @param array  $users
     */
    private function batchUpdateES($esHost, $gameVersion, array $users)
    {
        $count = count($users);
        if ($count === 0) {
            return;
        }
        appendLog(sprintf('%s have %d user to sync', __METHOD__, $count));

        $repo = $this->getESRepo($esHost, $gameVersion);
        assert($repo instanceof ESGatewayUserRepo);
        $factory = $repo->getFactory();
        assert($factory instanceof Factory);

        /** @var User[] $esUserList */
        $esUserList = [];
        foreach ($users as $user) {
            $esUserList[] = $factory->makeUser($user);
        }

        $batchSize = 200;
        $offset = 0;
        $totalDelta = 0;
        while (($batch = array_splice($esUserList, $offset, $batchSize))) {
            PHP_Timer::start();
            $repo->burst($batch);
            $delta = PHP_Timer::stop();
            $totalDelta += $delta;
            appendLog(
                sprintf(
                    '%s %f on slice(%d users) cost %s',
                    __METHOD__,
                    microtime(true),
                    count($batch),
                    PHP_Timer::secondsToTimeString($delta)
                )
            );
        }
        appendLog(
            sprintf(
                'Sync %d users to ES cost %s with average cost %s',
                $count,
                PHP_Timer::secondsToTimeString($totalDelta),
                PHP_Timer::secondsToTimeString($totalDelta / $count)
            )
        );
    }

    /**
     * @param string $host
     * @param string $gameVersion
     *
     * @return ESGatewayUserRepo
     */
    private function getESRepo($host, $gameVersion)
    {
        $builder = new ESGatewayBuilder();

        return $builder->buildUserRepo(
            [
                'host' => $host,
                'port' => 9200,
                'index' => 'farm',
                'type' => 'user:'.$gameVersion,
            ]
        );
    }

    /**
     * @param array $groupedUidList
     *
     * @return int
     */
    private function countUidList(array $groupedUidList)
    {
        $count = 0;
        array_map(function (array $list) use (&$count) {
            $count += count($list);
        }, $groupedUidList);

        return $count;
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
        assert(strpos($logFile, '/mnt/htdocs/notification/log/') === 0);
        $dirName = dirname($logFile);
        if (!is_dir($dirName)) {
            $success = mkdir($dirName, 0755, true);
            assert($success);
        }
    }
}
