<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 17:26.
 */
namespace unit\DataProvider;

use Database\PdoFactory;
use DataProvider\User\ActiveUidProvider;

/**
 * Class ActiveUidProviderTest.
 */
class ActiveUidProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function test()
    {
        if (extension_loaded('xdebug')) {
            $this->assertTrue(true);

            return;
        }
        $gameVersion = 'tw';

        $pool = PdoFactory::makePool($gameVersion);

        $provider = new ActiveUidProvider($gameVersion, $pool);
        $now = time();
        $groupedUidList = $provider->generate($now - 60);
        foreach ($groupedUidList as $shardId => $uidList) {
            static::assertStringStartsWith('db', $shardId);
            static::assertTrue(is_array($uidList));
            static::assertEquals(range(0, count($uidList) - 1), array_keys($uidList));
        }
    }
}
