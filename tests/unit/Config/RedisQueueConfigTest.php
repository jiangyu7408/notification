<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 11:50 AM
 */
namespace Config;

class RedisQueueConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testAttributes()
    {
        $object = new RedisQueueConfig();
        static::assertObjectHasAttribute('scheme', $object);
        static::assertObjectHasAttribute('host', $object);
        static::assertObjectHasAttribute('port', $object);
        static::assertObjectHasAttribute('timeout', $object);
        static::assertObjectHasAttribute('queueName', $object);
    }
}
