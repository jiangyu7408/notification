<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 1:24 PM
 */
namespace Persistency\Storage;

class RedisStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testFactory()
    {
        $prefix       = 'test';
        $factory      = new RedisStorageFactory();
        $redisStorage = $factory->create($this->options, $prefix);
        static::assertInstanceOf(RedisStorage::class, $redisStorage);
        static::assertEquals($prefix, $redisStorage->getPrefix());
    }

    protected function setUp()
    {
        $this->options = require __DIR__ . '/../../redis.php';
    }
}
