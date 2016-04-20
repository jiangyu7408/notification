<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:05.
 */
namespace DataProvider\User;

use Database\PdoFactory;
use Database\PdoPool;

/**
 * Class UserDetailProvider.
 */
class UserDetailProvider
{
    /** @var string */
    protected $gameVersion;
    /** @var PdoPool */
    protected $pdoPool;

    /**
     * UserDetailProvider constructor.
     *
     * @param string  $gameVersion
     * @param PdoPool $pdoPool
     */
    public function __construct($gameVersion, PdoPool $pdoPool)
    {
        $this->gameVersion = $gameVersion;
        $this->pdoPool = $pdoPool;
    }

    /**
     * @param array $groupedUidList
     *
     * @return array
     */
    public function generate(array $groupedUidList)
    {
        $userList = [];
        $snsidUidPairs = [];

        foreach ($groupedUidList as $shardId => $uidList) {
            $pdo = $this->pdoPool->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }
            $shardUserList = CommonInfoProvider::readUserInfo($pdo, $uidList);
            $userList[$shardId] = $shardUserList;
            $this->makeSnsidUidPairs($shardUserList, $snsidUidPairs);
        }

        return $this->appendPaymentDigest($userList, $snsidUidPairs);
    }

    /**
     * @param array $uidList
     *
     * @return array
     */
    public function find(array $uidList)
    {
        $userList = [];
        $snsidUidPairs = [];

        $shardIdList = $this->pdoPool->listShardId();
        foreach ($shardIdList as $shardId) {
            $pdo = $this->pdoPool->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }
            $shardUserList = CommonInfoProvider::readUserInfo($pdo, $uidList);
            $userList[$shardId] = $shardUserList;
            $this->makeSnsidUidPairs($shardUserList, $snsidUidPairs);
        }

        return $this->appendPaymentDigest($userList, $snsidUidPairs);
    }

    /**
     * @param array $userList
     * @param array $snsidUidPairs
     *
     * @return array
     */
    protected function appendPaymentDigest(array $userList, $snsidUidPairs)
    {
        $paymentDigestList = PaymentInfoProvider::readUserInfo(
            PdoFactory::makeGlobalPdo($this->gameVersion),
            $snsidUidPairs
        );

        $emptyPaymentDigest = new PaymentDigest();
        foreach ($userList as &$shardUserList) {
            foreach (array_keys($shardUserList) as $uid) {
                $paymentDigest = isset($paymentDigestList[$uid])
                    ? $paymentDigestList[$uid]
                    : $emptyPaymentDigest;
                $this->injectPaymentDigest($shardUserList[$uid], $paymentDigest);
            }
        }

        return $userList;
    }

    /**
     * @param array $shardUserList
     * @param array $snsidUidPairs
     *
     * @return array
     */
    protected function makeSnsidUidPairs(array $shardUserList, array &$snsidUidPairs)
    {
        foreach ($shardUserList as $user) {
            $snsidUidPairs[$user['snsid']] = $user['uid'];
        }
    }

    /**
     * @param array         $user
     * @param PaymentDigest $paymentDigest
     */
    protected function injectPaymentDigest(array &$user, PaymentDigest $paymentDigest)
    {
        $user['history_pay_amount'] = $paymentDigest->historyPayAmount;
        $user['last_pay_time'] = (int) $paymentDigest->lastPayTime;
        $user['last_pay_amount'] = $paymentDigest->lastPayAmount;
    }
}
