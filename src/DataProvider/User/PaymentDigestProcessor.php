<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 15:57.
 */
namespace DataProvider\User;

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

        $digest = new PaymentDigest();
        $digest->snsid = $snsid;
        $digest->uid = $uid;
        $digest->totalAmount = 0.0;

        foreach ($goodPaymentList as $payment) {
            $sanitizedAmount = $this->sanitizeAmount($payment);
            $digest->totalAmount += $sanitizedAmount;
            if ($digest->lastPaidTime < $payment->paidTime) {
                $digest->lastPaidTime = $payment->paidTime;
                $digest->lastPaidAmount = $sanitizedAmount;
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

        throw new \BadMethodCallException(sprintf('currency[%s] not support', $payment->currency));
    }
}
