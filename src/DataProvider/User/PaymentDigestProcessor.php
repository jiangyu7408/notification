<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 15:57.
 */
namespace DataProvider\User;

use DataProvider\Currency\CurrencyQuery;

/**
 * Class PaymentDigestProcessor.
 */
class PaymentDigestProcessor
{
    /**
     * @param string          $snsid
     * @param int             $uid
     * @param Payment[]       $paymentList
     * @param RefundPayment[] $refundList
     *
     * @return PaymentDigest
     */
    public function process($snsid, $uid, array $paymentList, array $refundList)
    {
        $goodPaymentList = $this->filterOutRefund($paymentList, $refundList);
        if (count($goodPaymentList) === 0) {
            return null;
        }

        $digest = new PaymentDigest();
        $digest->snsid = $snsid;
        $digest->uid = $uid;
        $digest->historyPayAmount = 0.0;

        foreach ($goodPaymentList as $payment) {
            $sanitizedAmount = $this->sanitizeAmount($payment);
            $digest->historyPayAmount += $sanitizedAmount;
            if ($digest->lastPayTime < $payment->paidTime) {
                $digest->lastPayTime = (int) $payment->paidTime;
                $digest->lastPayAmount = $sanitizedAmount;
            }
        }

        return $digest;
    }

    /**
     * @param Payment[]       $paymentList
     * @param RefundPayment[] $refundList
     *
     * @return Payment[]
     */
    protected function filterOutRefund(array $paymentList, array $refundList)
    {
        foreach (array_keys($refundList) as $paymentId) {
            unset($paymentList[$paymentId]);
        }

        return $paymentList;
    }

    /**
     * @param Payment $payment
     *
     * @return float
     */
    protected function sanitizeAmount(Payment $payment)
    {
        if ($payment->currency === 'USD') {
            return (float) $payment->amount;
        }

        try {
            $rate = CurrencyQuery::query($payment->currency);

            return ($rate * $payment->amount);
        } catch (\InvalidArgumentException $e) {
            appendLog(sprintf('currency[%s] rate fail: %s', $payment->currency, $e->getMessage()));
        }

        return 0.0;
    }
}
