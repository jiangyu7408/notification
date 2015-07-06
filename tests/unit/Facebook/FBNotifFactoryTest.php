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

        $fbNotifFactory = new FBNotifFactory();
        $fbNotif        = $fbNotifFactory->make($notif);
        static::assertInstanceOf('FBGateway\FBNotif', $fbNotif);

        $expectedArray = array();
        foreach ($fbNotif as $key => $value) {
            $expectedArray[$key] = $rawData[$key];
        }
        static::assertEquals($expectedArray, $fbNotifFactory->toArray($fbNotif));
    }

    public function testList()
    {
        $notifFactory = new NotifFactory();
        $notifList    = array();
        for ($i = 0; $i < 3; $i++) {
            $rawData     = array(
                'appid'    => 111,
                'snsid'    => '675097095878591' . '_' . $i,
                'feature'  => 'feature111',
                'template' => 'template111',
                'trackRef' => 'ref111',
                'fireTime' => time() + 100,
                'fired'    => false,
            );
            $notifList[] = $notifFactory->make($rawData);
        }

        $fbNotifFactory = new FBNotifFactory();
        $fbNotifList = $fbNotifFactory->makeList($notifList);
        static::assertTrue(is_array($fbNotifList) && count($fbNotifList) === count($notifList));

        $fbNotifList2 = array();
        foreach ($notifList as $notif) {
            $fbNotifList2[] = $fbNotifFactory->make($notif);
        }
        static::assertEquals($fbNotifList, $fbNotifList2);
    }

    protected function tearDown()
    {
    }

    protected function setUp()
    {
    }
}
