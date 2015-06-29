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
    protected $redis;
    /**
     * @var int
     */
    protected $fireTime;
    /**
     * @var RedisStorage
     */
    protected $storage;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function test()
    {
        $fireTime = $this->fireTime;
        $storage  = $this->storage;

        $success = $storage->add([
            'fireTime' => $fireTime,
            'snsid'    => '675097095878591'
        ]);
        static::assertTrue($success);

        $success = $storage->add([
            'fireTime' => $fireTime,
            'snsid'    => '675097095878591'
        ]);
        static::assertNotTrue($success);

        $list = $storage->getList($fireTime);
        static::assertTrue(is_array($list));
    }

    protected function tearDown()
    {
        $this->storage->purgeList($this->fireTime);
    }

    protected function setUp()
    {
        $this->redis = RedisFactory::create([
            'scheme'  => 'tcp',
            'host'    => '10.0.64.56',
            'port'    => 6379,
            'timeout' => 5.0,
        ]);

        $this->fireTime = time();

        $this->storage = new RedisStorage($this->redis, 'test');
    }
}
