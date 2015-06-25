<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 3:41 PM
 */
namespace BusinessEntity;

class BackflowFactoryTest extends \PHPUnit_Framework_TestCase
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

    public function test()
    {
        $factory  = new BackflowFactory();
        $rawData  = array(
            'appid'    => 111,
            'snsid'    => '675097095878591',
            'trackRef' => 'ref111'
        );
        $backflow = $factory->make($rawData);
        static::assertInstanceOf('BusinessEntity\Backflow', $backflow);
        static::assertEquals($rawData, $factory->toArray($backflow));
    }
}
