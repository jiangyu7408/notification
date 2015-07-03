<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 11:51 AM
 */
namespace Config;

class RedisConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testCreate()
    {
        static::assertTrue(is_array($this->options));

        $factory      = new RedisConfigFactory();
        $configObject = $factory->create($this->options);
        static::assertInstanceOf(RedisConfig::class, $configObject);

        $configString = $configObject->toString();
        static::assertRegExp('#^tcp://.+:[1-9][0-9]+$#', $configString);

        $array = $configObject->toArray();
        static::assertEquals($this->options, $array);
    }

    protected function setup()
    {
        $this->options = require __DIR__ . '/../../../redis.php';
    }
}
