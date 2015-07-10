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

    public function testBogus()
    {
        $factory = new RedisConfigFactory();

        try {
            $factory->create([]);
        } catch (\InvalidArgumentException $e) {
            static::assertTrue(strpos($e->getMessage(), 'bad config: check key') !== false);
        }
    }

    protected function setup()
    {
        $this->options = [
            'scheme'  => 'tcp',
            'host'    => '127.0.0.1',
            'port'    => 6379,
            'timeout' => 5.0
        ];
    }
}
