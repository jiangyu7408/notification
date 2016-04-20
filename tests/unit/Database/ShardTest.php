<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 13:48.
 */
namespace unit\Database;

use Database\PdoFactory;
use Database\PdoPool;
use Database\ShardHelper;

/**
 * Class ShardTest.
 */
class ShardTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testPool()
    {
        $pool = PdoFactory::makePool('tw');
        static::assertInstanceOf(PdoPool::class, $pool);

        $shardIdList = $pool->listShardId();
        static::assertEquals($shardIdList, ShardHelper::listShardId('tw'));
    }

    /**
     *
     */
    public function testFactory()
    {
        if (!extension_loaded('xdebug')) {
            $pdo = PdoFactory::makeGlobalPdo('tw');
            static::assertInstanceOf(\PDO::class, $pdo);
        }
    }

    /**
     *
     */
    public function testShardConfigGenerator()
    {
        $generator = ShardHelper::shardConfigGenerator('tw');
        static::assertInstanceOf(\Generator::class, $generator);
        foreach ($generator as $option) {
            static::assertTrue(is_array($option));
            static::assertArrayHasKey('shardId', $option);
        }
    }

    /**
     *
     */
    public function testListShardId()
    {
        $shardIdList = ShardHelper::listShardId('tw');
        $keys = array_keys($shardIdList);
        $expectedKeys = range(0, count($shardIdList) - 1);
        static::assertEquals($expectedKeys, $keys);
        array_map(
            function ($shardId) {
                static::assertStringStartsWith('db', $shardId);
            },
            $shardIdList
        );
    }

    /**
     *
     */
    public function testListShardOptions()
    {
        $options = ShardHelper::listShardOptions('tw');
        foreach ($options as $shardId => $option) {
            static::assertStringStartsWith('db', $shardId);
            static::assertTrue(is_array($option));
            static::assertArrayHasKey('shardId', $option);
        }
    }
}
