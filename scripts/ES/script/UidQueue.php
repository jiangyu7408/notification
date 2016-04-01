<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:03.
 */
namespace script;

use Queue\FileQueue;

/**
 * Class UidQueue.
 */
class UidQueue
{
    /**
     * UidQueue constructor.
     *
     * @param string $dir
     * @param string $gameVersion
     * @param array  $shardList
     */
    public function __construct($dir, $gameVersion, array $shardList)
    {
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException($dir.' not usable');
        }
        rtrim($dir, '/');
        array_map(
            function ($shardId) use ($dir) {
                $filePath = $dir.'/'.$shardId;
                if (!file_exists($filePath)) {
                    mkdir($filePath);
                }
            },
            $shardList
        );
        $this->dir = $dir;
        $this->gameVersion = $gameVersion;
        $this->shardList = $shardList;
    }

    /**
     * @param array $groupedUidList
     */
    public function push(array $groupedUidList)
    {
        array_walk(
            $groupedUidList,
            function (array $uidList, $shardId) {
                if (count($uidList) === 0) {
                    return;
                }
                $filePath = $this->getQueueFilePath($shardId);
                $fileQueue = new FileQueue($filePath);
                $fileQueue->push(implode(PHP_EOL, $uidList));
            }
        );
    }

    /**
     * @return array
     */
    public function pop()
    {
        $result = [];
        foreach ($this->shardList as $shardId) {
            $result[$shardId] = [];
        }
        array_map(
            function ($shardId) use (&$result) {
                $filePath = $this->getQueueFilePath($shardId);
                $fileQueue = new FileQueue($filePath);
                while (($data = $fileQueue->pop()) !== '') {
                    $result[$shardId] = array_merge($result[$shardId], explode(PHP_EOL, $data));
                }
            },
            $this->shardList
        );

        return $result;
    }

    /**
     * @param string $shardId
     *
     * @return string
     */
    private function getQueueFilePath($shardId)
    {
        return sprintf('%s/%s/%s', $this->dir, $this->gameVersion, $shardId);
    }
}
