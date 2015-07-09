<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 3:12 PM
 */
namespace Persistency\Storage;

use Predis\Client;

class RedisStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $redisClient;
    /**
     * @var int
     */
    protected $fireTime;
    /**
     * @var RedisStorage
     */
    protected $storage;
    /**
     * @var string
     */
    protected $prefix;

    public function test()
    {
        $fireTime = $this->fireTime;
        $storage  = $this->storage;

        $input = [
            [
                'fireTime' => $fireTime,
                'snsid'    => '675097095878591'
            ],
            [
                'fireTime' => $fireTime,
                'snsid'    => '675097095878591'
            ]
        ];

        $this->redisClient->shouldReceive('hset')->times(1)->andReturn(1);

        $ret = $storage->add($input[0]);
        static::assertEquals(1, $ret);

        $this->redisClient->shouldReceive('hset')->times(1)->andReturn(1);

        $ret = $storage->add($input[1]);
        static::assertEquals(1, $ret);

        $this->redisClient->shouldReceive('hgetall')->times(1)->andReturn([
            json_encode($input[0]),
            json_encode($input[1])
        ]);

        $list = $storage->getList($fireTime);
        static::assertTrue(is_array($list));
    }

    public function testNoFireTime()
    {
        try {
            $this->storage->add([
                'snsid' => '675097095878591'
            ]);
        } catch (\InvalidArgumentException $e) {
            static::assertEquals('payload should has key: fireTime, and >0', $e->getMessage());
        }
    }

    protected function tearDown()
    {
        $this->redisClient->shouldReceive('del')->times(1)->andReturn(1);
        $this->storage->purgeList($this->fireTime);
    }

    protected function setUp()
    {
        $this->fireTime    = time();
        $this->prefix      = 'test';
        $this->redisClient = \Mockery::mock(Client::class);
        $this->storage     = new RedisStorage($this->redisClient, $this->prefix);
        static::assertInstanceOf(RedisStorage::class, $this->storage);
    }
}
