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

    protected function tearDown()
    {
    }

    protected function setUp()
    {
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage bad config file
     */
    public function testBadConfig()
    {
        $config = array();
        (new FBGatewayBuilder())->buildParam($config);
    }

    public function testGoodConfig()
    {
        $config = require __DIR__ . '/../_fixture/fb.php';
        $factory = (new FBGatewayBuilder())->buildFactory($config['good']);
        static::assertInstanceOf(FBGatewayFactory::class, $factory);
    }
}
