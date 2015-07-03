<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 3:08 PM
 */
namespace Persistency\Storage;

use Config\RedisConfigFactory;
use Predis\Client;

class RedisClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $options;

    public function testFactory()
    {
        $configObject = (new RedisConfigFactory())->create($this->options);

        $factory     = new RedisClientFactory();
        $redisClient  = $factory->create($configObject);
        static::assertInstanceOf(Client::class, $redisClient);
        $redisClient2 = $factory->create($configObject);
        static::assertSame($redisClient, $redisClient2);
    }

    protected function setup()
    {
        $this->options = require __DIR__ . '/../../redis.php';
    }
}
