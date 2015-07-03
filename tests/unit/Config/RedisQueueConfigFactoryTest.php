<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 12:00 PM
 */
namespace Config;

class RedisQueueConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testCreate()
    {
        static::assertTrue(is_array($this->options));

        $factory      = new RedisQueueConfigFactory();
        $configObject = $factory->create($this->options);
        static::assertInstanceOf(RedisQueueConfig::class, $configObject);

        $configString = $factory->toString($configObject);
        static::assertRegExp('#^tcp://.+:[1-9][0-9]+$#', $configString);

        $array = $factory->toArray($configObject);
        static::assertEquals($this->options, $array);
    }

    protected function setUp()
    {
        $this->options = require __DIR__ . '/../../../redis_queue.php';
    }
}
