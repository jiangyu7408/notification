<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/05/10
 * Time: 14:39.
 */
namespace Facade\ES;

use DataProvider\User\UserDetailProvider;
use PHP_Timer;

/**
 * Class Manager.
 */
class Manager
{
    /** @var UserDetailProvider */
    protected $dataProvider;

    /**
     * Manager constructor.
     *
     * @param UserDetailProvider $dataProvider
     * @param Indexer            $indexer
     * @param int                $bufferLength
     */
    public function __construct(UserDetailProvider $dataProvider, Indexer $indexer, $bufferLength = 500)
    {
        $this->dataProvider = $dataProvider;
        $this->indexer = $indexer;
        $this->bufferLengthMax = $bufferLength;
    }

    /**
     * @param array $groupedUidList
     *
     * @return int
     */
    public function updateES(array $groupedUidList)
    {
        appendLog(sprintf('%s start with memory usage %s', __METHOD__, PHP_Timer::resourceUsage()));
        $count = array_sum(
            array_map(
                function (array $list) {
                    return count($list);
                },
                $groupedUidList
            )
        );
        if ($count === 0) {
            appendLog(sprintf('%s: have 0 user to sync', __METHOD__));

            return 0;
        }

        $start = microtime(true);
        $groupedDetail = $this->dataProvider->generate($groupedUidList);
        $delta = microtime(true) - $start;

        $totalUidCount = array_sum(
            array_map(
                function (array $uidList) {
                    return count($uidList);
                },
                $groupedUidList
            )
        );
        appendLog(
            sprintf(
                'Total %d uids, read detail cost %s',
                $totalUidCount,
                PHP_Timer::secondsToTimeString($delta)
            )
        );

        $bufferSyncAction = function (array $users) {
            $count = count($users);
            appendLog(sprintf('ES updater have %d user to sync', $count));
            $deltaList = $this->indexer->batchUpdate($users, function ($userCount, $delta) {
                appendLog(
                    sprintf(
                        'on ES batch update of %d users cost %s',
                        $userCount,
                        PHP_Timer::secondsToTimeString($delta)
                    )
                );
            });
            $totalDelta = array_sum($deltaList);
            appendLog(
                sprintf(
                    'Sync %d users to ES cost %s with average cost %s',
                    $count,
                    PHP_Timer::secondsToTimeString($totalDelta),
                    PHP_Timer::secondsToTimeString($totalDelta / $count)
                )
            );
        };

        $buffer = new WriteBuffer($bufferSyncAction, 500);
        foreach ($groupedDetail as $payload) {
            $shardId = $payload['shardId'];
            $shardUserList = $payload['dataSet'];

            $count = count($shardUserList);
            if ($count === 0) {
                continue;
            }

            appendLog(sprintf('%s have %d user to sync', $shardId, $count));
            foreach ($shardUserList as $userInfo) {
                $buffer->add($userInfo);
            }
        }
        $buffer->sync();
    }
}
