<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 3:47 PM.
 */

namespace BusinessEntity;

class NotifFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $factory = new NotifFactory();
        $rawData = [
            'appid' => 111,
            'snsid' => '675097095878591',
            'feature' => 'feature111',
            'template' => 'template111',
            'trackRef' => 'ref111',
            'fireTime' => time() + 100,
            'fired' => false,
        ];
        $notif = $factory->make($rawData);
        static::assertInstanceOf('BusinessEntity\Notif', $notif);
        static::assertEquals($rawData, $factory->toArray($notif));

        $firedNotif = $factory->markFired($notif);
        static::assertSame($firedNotif, $notif);
        static::assertTrue($notif->fired);
    }
}
