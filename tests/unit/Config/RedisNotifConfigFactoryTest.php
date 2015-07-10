<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 2:11 PM.
 */
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Config;

class RedisNotifConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testCreate()
    {
        $factory = new RedisNotifConfigFactory();

        $prefix = 'test';

        $redisConfig = (new RedisConfigFactory())->create($this->options);
        $configObject = $factory->create($redisConfig, $prefix);
        static::assertInstanceOf(RedisNotifConfig::class, $configObject);
        static::assertEquals($prefix, $configObject->prefix);
    }

    protected function setup()
    {
        $this->options = require __DIR__.'/../../../redis.php';
    }
}
