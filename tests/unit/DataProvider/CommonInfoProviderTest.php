<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 15:46.
 */
namespace unit\DataProvider;

use Database\PdoFactory;
use Database\ShardHelper;
use DataProvider\User\CommonInfoProvider;

/**
 * Class CommonInfoProviderTest.
 */
class CommonInfoProviderTest extends \PHPUnit_Framework_TestCase
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

        $pool = PdoFactory::makePool($gameVersion);
        $shardIdList = ShardHelper::listShardId($gameVersion);

        $uidList = [474000, 474001, 474002];

        $userList = [];
        foreach ($shardIdList as $shardId) {
            $pdo = $pool->getByShardId($shardId);
            $generator = CommonInfoProvider::readUserInfo($pdo, $uidList, 1);
            foreach ($generator as $batchUserList) {
                array_walk(
                    $batchUserList,
                    function (array $userInfo, $uid) use (&$userList) {
                        $userList[$uid] = $userInfo;
                    }
                );
            }
        }

        static::assertEquals($uidList, array_keys($userList));
        foreach ($userList as $user) {
            static::assertTrue(is_array($user));
            static::assertArrayHasKey('uid', $user);
            static::assertArrayHasKey('snsid', $user);
        }
    }
}
