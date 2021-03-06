<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 11:11.
 */
namespace DataProvider\User;

use PDO;

/**
 * Class PaymentInfoProvider.
 */
class PaymentInfoProvider
{
    const PAYMENT_ID = 'paymentId';

    /**
     * $snsidUidPairs MUST BE associate array: ['snsid' => 'uid']
     *
     * @param PDO   $pdo
     * @param array $snsidUidPairs
     * @param int   $concurrentLevel
     *
     * @return PaymentDigest[]
     */
    public static function readUserInfo(PDO $pdo, array $snsidUidPairs, $concurrentLevel = 100)
    {
        /** @var PaymentDigest[] $result */
        $result = [];

        $snsidList = array_keys($snsidUidPairs);
        if ($snsidList == range(0, count($snsidUidPairs) - 1)) {
            throw new \InvalidArgumentException('snsidUidPairs must be snsid => uid');
        }

        while (($concurrent = array_splice($snsidList, 0, $concurrentLevel))) {
            $batch = [];
            array_walk(
                $concurrent,
                function ($snsid) use ($snsidUidPairs, &$batch) {
                    $batch[$snsid] = $snsidUidPairs[$snsid];
                }
            );
            self::onBatch($pdo, $batch, $result);
        }

        return $result;
    }

    /**
     * @param PDO   $pdo
     * @param array $uidList
     * @param array $result
     */
    protected static function onBatch(PDO $pdo, array $uidList, array &$result)
    {
        $snsidList = array_keys($uidList);
        $allRefundList = self::fetchRefund($pdo, $snsidList);
        $allPaymentList = self::fetchPayments($pdo, $snsidList);

        $processor = new PaymentDigestProcessor();

        foreach ($uidList as $snsid => $uid) {
            /** @var RefundPayment[] $refundList */
            $refundList = isset($allRefundList[$snsid]) ? $allRefundList[$snsid] : [];

            /** @var Payment[] $paymentList */
            $paymentList = isset($allPaymentList[$snsid]) ? $allPaymentList[$snsid] : [];
            $result[$uid] = $processor->process($snsid, $uid, $paymentList, $refundList);
        }
    }

    /**
     * @param PDO      $pdo
     * @param string[] $snsidList
     *
     * @return RefundPayment[][]
     */
    protected static function fetchRefund(PDO $pdo, array $snsidList)
    {
        $columns = sprintf('snsid,paymentId as %s,amount/100 as amount,currency,time', self::PAYMENT_ID);
        $placeHolderList = array_pad([], count($snsidList), '?');
        $sql = sprintf(
            'select %s from tbl_payment_refund where snsId in (%s)',
            $columns,
            implode(',', $placeHolderList)
        );
        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new \RuntimeException(json_encode($pdo->errorInfo()));
        }
        $success = $statement->execute($snsidList);
        assert($success);

        /** @var RefundPayment[] $result */
        $result = [];
        $prototype = new RefundPayment();
        $vars = array_keys(get_object_vars($prototype));

        $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $row) {
            $data = clone $prototype;
            foreach ($vars as $field) {
                assert(array_key_exists($field, $row));
                $data->{$field} = $row[$field];
            }
            if (!array_key_exists($data->snsid, $result)) {
                $result[$data->snsid] = [];
            }
            $result[$data->snsid][$data->paymentId] = $data;
        }
        $statement->closeCursor();

        return $result;
    }

    /**
     * @param PDO   $pdo
     * @param array $snsidList
     *
     * @return Payment[][]
     */
    protected static function fetchPayments(PDO $pdo, array $snsidList)
    {
        $columns = sprintf('uid as snsid,type as %s,currency,amount/100 as amount,paid_time as paidTime', self::PAYMENT_ID);
        $placeHolderList = array_pad([], count($snsidList), '?');
        $sql = sprintf(
            'select %s from tbl_payments where uid in (%s)',
            $columns,
            implode(',', $placeHolderList)
        );
        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new \RuntimeException(json_encode($pdo->errorInfo()));
        }
        $success = $statement->execute($snsidList);
        assert($success);

        /** @var Payment[] $result */
        $result = [];
        $prototype = new Payment();
        $vars = array_keys(get_object_vars($prototype));

        $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $row) {
            $data = clone $prototype;
            foreach ($vars as $field) {
                assert(array_key_exists($field, $row), sprintf('on field %s', $field));
                $data->{$field} = $row[$field];
            }
            if (!array_key_exists($data->snsid, $result)) {
                $result[$data->snsid] = [];
            }
            $result[$data->snsid][$data->paymentId] = $data;
        }
        $statement->closeCursor();

        return $result;
    }
}
