<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 3:12 PM
 */
namespace Persistency\Storage;

use Config\RedisConfigFactory;
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
        $options = require __DIR__ . '/../_fixture/redisConfig.php';
        static::assertTrue(is_array($options), 'redisConfig.php not working');
        $configObject = (new RedisConfigFactory())->create($options);

        $this->fireTime = time();

        $this->storage = (new RedisStorageFactory())->create($configObject);
        static::assertInstanceOf(RedisStorage::class, $this->storage);
    }
}
