<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:47 PM.
 */
namespace Worker\Queue;

use Worker\Model\Request;

/**
 * Class RetryQueue.
 */
class RetryQueue
{
    /**
     * @var int
     */
    protected $maxTry;
    /**
     * @var Request[]
     */
    protected $list = [];

    /**
     * RetryQueue constructor.
     *
     * @param int $maxTry
     */
    public function __construct($maxTry = 3)
    {
        $this->maxTry = $maxTry;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function add(Request $request)
    {
        $this->initSlotIfPossible($request);
        if ($this->isMaxRetryReached($request)) {
            return false;
        }
        $this->incrRetry($request);

        return true;
    }

    /**
     * @param Request $request
     */
    protected function initSlotIfPossible(Request $request)
    {
        if (array_key_exists($request->url, $this->list)) {
            return;
        }

        $this->list[$request->url] = [
            'request' => $request,
            'step' => 0,
        ];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isMaxRetryReached(Request $request)
    {
        return ($this->list[$request->url]['step'] < $this->maxTry);
    }

    /**
     * @param Request $request
     */
    protected function incrRetry(Request $request)
    {
        ++$this->list[$request->url]['step'];
    }
}
