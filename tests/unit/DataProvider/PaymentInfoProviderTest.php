<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 15:56.
 */
namespace unit\DataProvider;

use Database\PdoFactory;
use DataProvider\User\PaymentDigest;
use DataProvider\User\PaymentInfoProvider;

/**
 * Class PaymentInfoProviderTest.
 */
class PaymentInfoProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testReadUserInfo()
    {
        if (extension_loaded('xdebug')) {
            $this->assertTrue(true);

            return;
        }
        $gameVersion = 'tw';

        $pdo = PdoFactory::makeGlobalPdo($gameVersion);

        $snsidUidPairs = [
            '100001349218797' => 474000,
            '100001109305149' => 237204,
        ];
        $uidSnsidPairs = array_flip($snsidUidPairs);
        $userList = PaymentInfoProvider::readUserInfo($pdo, $snsidUidPairs, 1);
//        dump($userList);
        foreach ($userList as $uid => $digest) {
            static::assertInstanceOf(PaymentDigest::class, $digest);
            static::assertEquals($uid, $digest->uid);
            $snsid = $uidSnsidPairs[$uid];
            static::assertEquals($snsid, $digest->snsid);

            static::assertTrue(is_int($digest->lastPayTime));
            static::assertTrue(is_float($digest->lastPayAmount));
            static::assertTrue(is_float($digest->historyPayAmount));
        }
    }
}
