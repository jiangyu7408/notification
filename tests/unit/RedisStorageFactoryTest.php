<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 1:24 PM
 */
namespace Persistency\Storage;

use Config\RedisConfigFactory;

class RedisStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testFactory()
    {
        $configObject = (new RedisConfigFactory())->create($this->options);

        $factory      = new RedisStorageFactory();
        $redisStorage = $factory->create($configObject);
        static::assertInstanceOf(RedisStorage::class, $redisStorage);
    }

    protected function setUp()
    {
        $this->options = require __DIR__ . '/../../redis.php';
    }
}
