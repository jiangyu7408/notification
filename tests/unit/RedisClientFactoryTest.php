<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 3:08 PM
 */
namespace Persistency\Storage;

use Predis\Client;

class RedisClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function testFactory()
    {
        $factory     = new RedisClientFactory();
        $redisClient = $factory->create();
        static::assertInstanceOf(Client::class, $redisClient);
        $redisClient2 = $factory->create();
        static::assertSame($redisClient, $redisClient2);
    }

    protected function tearDown()
    {
    }

    protected function setUp()
    {
    }
}
