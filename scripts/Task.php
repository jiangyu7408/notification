<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:04 PM
 */

/**
 * Class Task
 * @package ${NAMESPACE}
 */
class Task
{
    protected $taskId;
    protected $coroutine;
    protected $payload;
    protected $beforeFirstYield = true;

    public function __construct($taskId, Generator $coroutine)
    {
        $this->taskId    = $taskId;
        $this->coroutine = $coroutine;
    }

    public function getTaskId()
    {
        return $this->taskId;
    }

    public function setPayload($sendValue)
    {
        $this->payload = $sendValue;
    }

    public function run()
    {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        }
        $retVal        = $this->coroutine->send($this->payload);
        $this->payload = null;
        return $retVal;
    }

    public function isFinished()
    {
        return !$this->coroutine->valid();
    }
}
