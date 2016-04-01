<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:06.
 */
namespace script;

/**
 * Class UidAggregator.
 */
class UidAggregator
{
    /** @var AggregatorPersist */
    protected $persist;
    protected $queue = [];
    protected $activity = [];

    /**
     * UidAggregator constructor.
     *
     * @param AggregatorPersist $persist
     */
    public function __construct(AggregatorPersist $persist)
    {
        $this->persist = $persist;
        $this->loadProgress();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->saveProgress();
    }

    /**
     * @param string $shardId
     * @param array  $uidList
     * @param int    $timestamp
     */
    public function add($shardId, array $uidList, $timestamp)
    {
        $this->prepareShard($shardId, $this->queue);
        $this->prepareShard($shardId, $this->activity);
        $queue = &$this->queue[$shardId];
        $activity = &$this->activity[$shardId];
        array_walk(
            $uidList,
            function ($uid) use (&$queue, &$activity, $timestamp) {
                if (!array_key_exists($uid, $queue)) {
                    $queue[$uid] = [];
                }
                $queue[$uid][] = $timestamp;
                if (!array_key_exists($uid, $activity)) {
                    $activity[$uid] = $timestamp;
                }
            }
        );
    }

    /**
     * @param int $queueLength
     * @param int $beforeTs
     *
     * @return array
     */
    public function filter($queueLength, $beforeTs)
    {
        $uidList = [];
        foreach ($this->queue as $shardId => $list) {
            $this->prepareShard($shardId, $uidList);
            $container = &$uidList[$shardId];
            array_walk(
                $list,
                function (array $queue, $uid) use ($queueLength, &$container) {
                    if (count($queue) >= $queueLength) {
                        $container[$uid] = count($queue);
                    }
                }
            );
        }
        foreach ($this->activity as $shardId => $list) {
            $this->prepareShard($shardId, $uidList);
            $container = &$uidList[$shardId];
            array_walk(
                $list,
                function ($timestamp, $uid) use ($beforeTs, &$container) {
                    if ($timestamp > $beforeTs) {
                        return;
                    }
                    if (array_key_exists($uid, $container)) {
                        return;
                    }
                    $container[$uid] = $timestamp;
                }
            );
        }
        foreach ($uidList as $shardId => $list) {
            array_map(
                function ($uid) use ($shardId) {
                    unset($this->queue[$shardId][$uid]);
                },
                array_keys($list)
            );
            array_map(
                function ($uid) use ($shardId) {
                    unset($this->activity[$shardId][$uid]);
                },
                array_keys($list)
            );
        }

        return $uidList;
    }

    protected function prepareShard($shardId, array &$input)
    {
        if (!array_key_exists($shardId, $input)) {
            $input[$shardId] = [];
        }
    }

    protected function loadProgress()
    {
        $progress = $this->persist->load();
        if (array_key_exists('queue', $progress)) {
            $this->queue = $progress['queue'];
        }
        if (array_key_exists('activity', $progress)) {
            $this->activity = $progress['activity'];
        }
    }

    protected function saveProgress()
    {
        $progress = [
            'queue' => $this->queue,
            'activity' => $this->activity,
        ];
        $this->persist->save($progress);
    }
}
