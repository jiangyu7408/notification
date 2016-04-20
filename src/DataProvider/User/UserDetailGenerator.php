<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:05.
 */
namespace DataProvider\User;

use Database\PdoFactory;
use Environment\PlatformFactory;
use PDO;

/**
 * Class UserDetailGenerator.
 */
class UserDetailGenerator
{
    /** @var PDO */
    protected static $globalShard = null;

    /**
     * @param string $gameVersion
     * @param array  $groupedUidList
     *
     * @return array
     */
    public static function generate($gameVersion, array $groupedUidList)
    {
        $generator = PlatformFactory::make($gameVersion)->getMySQLShards();
        self::preparePaymentPdo($gameVersion);

        $shardConfigList = [];
        foreach ($generator as $shardConfig) {
            $shardConfigList[$shardConfig['shardId']] = $shardConfig;
        }

        $userList = [];
        foreach ($groupedUidList as $shardId => $uidList) {
            $pdo = PdoFactory::makePool($gameVersion)->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }
            $userList[$shardId] = CommonInfoProvider::readUserInfo($pdo, $uidList);
        }

        return $userList;
    }

    /**
     * @param string $gameVersion
     * @param array  $uidList
     *
     * @return array
     */
    public static function find($gameVersion, array $uidList)
    {
        $generator = PlatformFactory::make($gameVersion)->getMySQLShards();
        self::preparePaymentPdo($gameVersion);

        $userList = [];
        $idList = [];
        foreach ($generator as $shardConfig) {
            $shardId = $shardConfig['shardId'];
            $pdo = PdoFactory::makePool($gameVersion)->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }
            $common = CommonInfoProvider::readUserInfo($pdo, $uidList);
            $userList[$shardId] = $common;

            foreach ($common as $user) {
                $idList[$user['snsid']] = $user['uid'];
            }
        }

        $paymentDigestList = PaymentInfoProvider::readUserInfo(self::$globalShard, $idList);

        foreach ($userList as &$common) {
            foreach (array_keys($common) as $uid) {
                if (!isset($paymentDigestList[$uid])) {
                    continue;
                }
                $common[$uid]['history_pay_amount'] = $paymentDigestList[$uid]->totalAmount;
                $common[$uid]['last_pay_time'] = (int) $paymentDigestList[$uid]->lastPaidTime;
                $common[$uid]['last_pay_amount'] = $paymentDigestList[$uid]->lastPaidAmount;
            }
        }

        return $userList;
    }

    /**
     * @param string $gameVersion
     *
     * @return false|PDO
     */
    protected static function preparePaymentPdo($gameVersion)
    {
        if (self::$globalShard === null) {
            self::$globalShard = PdoFactory::makeGlobalPdo($gameVersion);
        }
        assert(self::$globalShard instanceof PDO);

        return self::$globalShard;
    }
}
