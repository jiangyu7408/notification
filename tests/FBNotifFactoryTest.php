<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 4:35 PM
 */
namespace FBGateway;

use BusinessEntity\NotifFactory;

class FBNotifFactoryTest extends \PHPUnit_Framework_TestCase
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
        $notifFactory = new NotifFactory();
        $rawData      = array(
            'appid'    => 111,
            'snsid'    => '675097095878591',
            'feature'  => 'feature111',
            'template' => 'template111',
            'trackRef' => 'ref111',
            'fireTime' => time() + 100,
            'fired'    => false,
        );
        $notif        = $notifFactory->make($rawData);

        $factory = new FBNotifFactory();
        $fbNotif = $factory->make($notif);
        static::assertInstanceOf('FBGateway\FBNotif', $fbNotif);

        $expectedArray = array();
        foreach ($fbNotif as $key => $value) {
            $expectedArray[$key] = $rawData[$key];
        }
        static::assertEquals($expectedArray, $factory->toArray($fbNotif));
    }
}
