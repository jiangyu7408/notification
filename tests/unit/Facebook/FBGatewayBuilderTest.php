<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 12:55 PM
 */
namespace FBGateway;

class FBGatewayBuilderTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function testBadConfig()
    {
        try {
            (new FactoryBuilder())->buildParam([]);
        } catch (\InvalidArgumentException $e) {
            static::assertInstanceOf(\Exception::class, $e);
            static::assertEquals('bad config file', $e->getMessage());
        }
    }

    public function testGoodConfig()
    {
        $config = require __DIR__ . '/../../_fixture/fb.php';
        $factory = (new FactoryBuilder())->create($config['good']);
        static::assertInstanceOf(Factory::class, $factory);
    }

    protected function tearDown()
    {
    }

    protected function setUp()
    {
    }
}
