<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 1:34 PM
 */
namespace Persistency\Storage;

class RedisNotifPersistTest extends \PHPUnit_Framework_TestCase
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

    public function testRetrieve()
    {
        static::assertInstanceOf(RedisStorage::class, $this->redisStorage);
        $persist = new RedisNotifPersist($this->redisStorage);

        set_error_handler([$this, 'assertUserWarning'], E_USER_WARNING);
        $persist->retrieve();
    }

    public function assertUserWarning($errno)
    {
        static::assertTrue($errno === E_USER_WARNING);
    }

    /*
    public function testPersist()
    {
        static::markTestSkipped();
        static::assertInstanceOf(RedisStorage::class, $this->redisStorage);
        $persist = new RedisNotifPersist($this->redisStorage);

        $payload = [
            'fireTime' => $this->fireTime,
            'snsid'    => '675097095878591'
        ];

        $ret = $persist->persist($payload);
        static::assertTrue($ret === true);

        $list = $this->redisStorage->getList($this->fireTime);
        foreach ($list as $each) {
            static::assertEquals($payload, $each);
        }
    }
    */

    protected function setup()
    {
        $options = require __DIR__ . '/../../redis.php';

        $this->prefix = 'test';

        $this->redisStorage = (new RedisStorageFactory())->create($options, $this->prefix);

        $this->fireTime = 1000;
        $this->redisStorage->purgeList($this->fireTime);
    }

    protected function tearDown()
    {
        $this->redisStorage->purgeList($this->fireTime);
    }
}
