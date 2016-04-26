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
     * @return \Generator
     */
    public function generate(array $groupedUidList)
    {
        foreach ($groupedUidList as $shardId => $shardUidList) {
            $pdo = $this->pdoPool->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }

            $batchReader = CommonInfoProvider::readUserInfo($pdo, $shardUidList, 500);
            foreach ($batchReader as $batchUserList) {
                $dataSet = $this->appendPaymentDigest($batchUserList);
                $uidLocalePairs = QueryHelperFactory::make($pdo)->listLocale(array_keys($batchUserList));
                foreach ($uidLocalePairs as $uid => $locale) {
                    $dataSet[$uid]['language'] = $locale;
                }
                yield ['shardId' => $shardId, 'dataSet' => $dataSet];
            }
        }
    }

    /**
     * @param array $uidList
     *
     * @return array
     */
    public function find(array $uidList)
    {
        if (count($uidList) > 500) {
            trigger_error('Max 500 users as input args');
        }

        $userList = [];
        $shardIdList = $this->pdoPool->listShardId();
        foreach ($shardIdList as $shardId) {
            $pdo = $this->pdoPool->getByShardId($shardId);
            if ($pdo === false) {
                continue;
            }

            $shardUserList = [];
            $batchReader = CommonInfoProvider::readUserInfo($pdo, $uidList);
            foreach ($batchReader as $batchUserList) {
                $batchDataSet = $this->appendPaymentDigest($batchUserList);
                foreach ($batchDataSet as $uid => $userInfo) {
                    $shardUserList[$uid] = $userInfo;
                }
            }
            $userList[$shardId] = $shardUserList;
        }

        return $userList;
    }

    /**
     * @param array $userList
     *
     * @return array
     */
    protected function appendPaymentDigest(array $userList)
    {
        $snsidUidPairs = [];
        foreach ($userList as $uid => $userInfo) {
            assert((int) $uid === (int) $userInfo['uid']);
            $snsidUidPairs[$userInfo['snsid']] = $userInfo['uid'];
        }

        $paymentDigestList = PaymentInfoProvider::readUserInfo(
            PdoFactory::makeGlobalPdo($this->gameVersion),
            $snsidUidPairs
        );
        $emptyPaymentDigest = new PaymentDigest();

        $dataSet = [];
        foreach ($userList as $uid => $userInfo) {
            $paymentDigest = isset($paymentDigestList[$uid]) ? $paymentDigestList[$uid] : $emptyPaymentDigest;
            $dataSet[$uid] = array_merge($userInfo, $this->injectPaymentDigest($paymentDigest));
        }

        return $dataSet;
    }

    /**
     * @param PaymentDigest $paymentDigest
     *
     * @return array
     */
    protected function injectPaymentDigest(PaymentDigest $paymentDigest)
    {
        return [
            'history_pay_amount' => $paymentDigest->historyPayAmount,
            'last_pay_time' => (int) $paymentDigest->lastPayTime,
            'last_pay_amount' => $paymentDigest->lastPayAmount,
        ];
    }
}
