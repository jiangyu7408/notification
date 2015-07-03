<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 11:48 AM
 */
namespace Config;

class RedisConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $object = new RedisConfig();
        static::assertObjectHasAttribute('scheme', $object);
        static::assertObjectHasAttribute('host', $object);
        static::assertObjectHasAttribute('port', $object);
        static::assertObjectHasAttribute('timeout', $object);
    }
}
