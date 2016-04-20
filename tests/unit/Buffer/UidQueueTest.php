<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 14:51.
 */
namespace unit\Buffer;

use Buffer\UidQueue;
use Database\ShardHelper;

/**
 * Class UidQueueTest.
 */
class UidQueueTest extends \PHPUnit_Framework_TestCase
{
    protected static $dir;
    protected static $gameVersion;

    /**
     *
     */
    public static function setUpBeforeClass()
    {
        self::$gameVersion = 'tw';
        self::$dir = __DIR__.'/fixture';
        if (!is_dir(self::$dir)) {
            $success = mkdir(self::$dir);
            static::assertTrue($success);
        }
    }

    /**
     *
     */
    public static function tearDownAfterClass()
    {
        self::cleanUp(self::$dir);
    }

    /**
     *
     */
    public function test()
    {
        $gameVersion = self::$gameVersion;
        $shardIdList = ShardHelper::listShardId($gameVersion);
        $queue = new UidQueue(self::$dir, $gameVersion, $shardIdList);
        $groupedUidList = [
            'db1' => [1, 2, 3],
            'db2' => [21, 22, 23],
        ];
        $queue->push($groupedUidList);
        $content = $queue->pop();

        foreach ($groupedUidList as $shardId => $expectedUidList) {
            static::assertEquals($expectedUidList, $content[$shardId]);
        }
    }

    /**
     * @param string $dir
     */
    protected static function cleanUp($dir)
    {
        $subDirs = self::listDir($dir);
        if ($subDirs) {
            foreach ($subDirs as $subDir) {
                self::cleanUp($dir.'/'.$subDir);
            }
        }
        $success = rmdir($dir);
        static::assertTrue($success);
    }

    /**
     * @param string $dir
     *
     * @return array
     */
    protected static function listDir($dir)
    {
        $subDirs = scandir($dir);
        do {
            $item = array_shift($subDirs);
            if (!in_array($item, ['.', '..'])) {
                array_unshift($subDirs, $item);
                break;
            }
        } while ($subDirs);

        return $subDirs;
    }
}
