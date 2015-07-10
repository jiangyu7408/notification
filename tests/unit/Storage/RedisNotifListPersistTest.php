<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/09
 * Time: 6:30 PM
 */
namespace Persistency\Storage;

use Mockery;

class RedisNotifListPersistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedisStorage
     */
    protected $redisStorage;
    /**
     * @var int
     */
    protected $fireTime;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var NotifArchiveStorage
     */
    protected $archiveStorage;

    public function testRetrieve()
    {
        static::assertInstanceOf(RedisStorage::class, $this->redisStorage);

        $persist = new RedisNotifListPersist($this->redisStorage, $this->archiveStorage);

        $persist->setFireTime($this->fireTime);

        set_error_handler([$this, 'assertUserWarning'], E_USER_WARNING);
        $this->redisStorage->shouldReceive('getList')->times(1);
        $persist->retrieve();
    }

    public function assertUserWarning($errno)
    {
        static::assertTrue($errno === E_USER_WARNING);
    }

    public function testPersist()
    {
        static::assertInstanceOf(RedisStorage::class, $this->redisStorage);
        $persist = new RedisNotifListPersist($this->redisStorage, $this->archiveStorage);

        $payload = [
            'fireTime' => $this->fireTime,
            'snsid'    => '675097095878591'
        ];

        $this->redisStorage->shouldReceive('add')->times(1)->andReturn(1);
        $ret = $persist->persist($payload);
        static::assertTrue($ret === true);

        $this->redisStorage->shouldReceive('getList')->times(1)->andReturn([$payload]);
        $list = $this->redisStorage->getList($this->fireTime);
        foreach ($list as $each) {
            static::assertEquals($payload, $each);
        }
    }

    protected function setup()
    {
        $this->archiveStorage = new NotifArchiveStorage();
        $this->prefix         = 'test';
        $this->redisStorage   = Mockery::mock(RedisStorage::class);

        $this->fireTime = 1000;
        $this->redisStorage->shouldReceive('purgeList')->times(1)->andReturn(true);
        $this->redisStorage->purgeList($this->fireTime);
    }

    protected function tearDown()
    {
        $this->redisStorage->shouldReceive('purgeList')->times(1)->andReturn(true);
        $this->redisStorage->purgeList($this->fireTime);
    }
}
