<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 16:19.
 */
namespace unit\DataProvider;

use Database\PdoFactory;
use DataProvider\User\UserDetailProvider;

/**
 * Class UserDetailProviderTest.
 */
class UserDetailProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testGenerate()
    {
        if (extension_loaded('xdebug')) {
            $this->assertTrue(true);

            return;
        }
        $gameVersion = 'tw';

        $pool = PdoFactory::makePool($gameVersion);

        $groupedUidList = [
            'db3' => [474000, 474001, 474002],
        ];

        $payload = [];
        $provider = new UserDetailProvider($gameVersion, $pool);
        $batchReader = $provider->generate($groupedUidList);
        foreach ($batchReader as $batch) {
            $shardId = $batch['shardId'];
            $this->assertStringStartsWith('db', $shardId);
            $shardDataSet = $batch['dataSet'];
            $this->assertTrue(is_array($shardDataSet));
            foreach ($shardDataSet as $uid => $userInfo) {
                $payload[$shardId][$uid] = $userInfo;
            }
        }

        $foundUidList = [];
        foreach ($payload as $shardId => $userList) {
            $foundUidList[$shardId] = array_keys($userList);
            foreach ($userList as $user) {
                static::assertTrue(is_array($user));
                static::assertArrayHasKey('uid', $user);
                static::assertArrayHasKey('snsid', $user);
            }
        }

        sort($groupedUidList);
        sort($foundUidList);
        static::assertEquals($groupedUidList, $foundUidList);
    }

    /**
     *
     */
    public function testFind()
    {
        if (extension_loaded('xdebug')) {
            $this->assertTrue(true);

            return;
        }
        $gameVersion = 'tw';

        $pool = PdoFactory::makePool($gameVersion);

        $uidList = [474000, 474001, 474002];

        $provider = new UserDetailProvider($gameVersion, $pool);
        $payload = array_filter($provider->find($uidList));

        $foundUidList = [];
        foreach ($payload as $shardId => $userList) {
            static::assertStringStartsWith('db', $shardId);
            $foundUidList = array_merge($foundUidList, array_keys($userList));
            foreach ($userList as $user) {
                static::assertTrue(is_array($user));
                static::assertArrayHasKey('uid', $user);
                static::assertArrayHasKey('snsid', $user);
                static::assertArrayHasKey('history_pay_amount', $user);
            }
        }

        sort($uidList);
        sort($foundUidList);
        static::assertEquals($uidList, $foundUidList);
    }
}
