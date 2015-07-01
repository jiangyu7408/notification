<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 11:51 AM
 */

namespace Worker;

use Worker\Model\RequestFactory;
use Worker\Model\Response;
use Worker\Model\Task;
use Worker\Queue\RetryQueue;
use Worker\Queue\RunningQueue;
use Worker\Queue\TaskProvider;

/**
 * Class CurlWorker
 * @package Worker
 */
class CurlWorker
{
    /**
     * @var TaskProvider
     */
    protected $dataProvider;
    /**
     * @var RetryQueue
     */
    protected $retryQueue;
    /**
     * @var RunningQueue
     */
    protected $runningQueue;

    public function __construct()
    {
        $this->requestFactory = new RequestFactory();
    }

    /**
     * @param array $tasks
     * @param int $concurrency
     * @return bool
     */
    public function addTasks(array $tasks, $concurrency = 10)
    {
        if (($this->dataProvider instanceof TaskProvider) && (!$this->dataProvider->isEmpty())) {
            return false;
        }

        $this->dataProvider = new TaskProvider($tasks);
        $this->retryQueue   = new RetryQueue();

        $this->runningQueue = new RunningQueue($this->dataProvider, $concurrency);
        while ($this->runningQueue->canAdd()
               && ($task = $this->dataProvider->nextTask())) {
            $request = $this->makeRequest($task);
            $this->runningQueue->add($request);
        }

        return true;
    }

    protected function makeRequest(Task $task)
    {
        return $this->requestFactory->create($task->getOptions());
    }

    public function run()
    {
        $retry   = 0;
        $fail    = 0;
        $success = 0;

        $pendingResponseList = $this->runningQueue->run();
        /** @var Response $pendingResponse */
        foreach ($pendingResponseList as $pendingResponse) {
            $success = $this->handleResponseIfSuccess($pendingResponse);
            if ($success) {
                $success++;
                continue;
            }

            $this->handleResponseIfFail($pendingResponse, $fail, $retry);
        }

        echo PHP_EOL . "success: {$success}, retry: {$retry}, fail: {$fail}" . PHP_EOL;
    }

    /**
     * @param Response $pendingResponse
     * @return bool
     */
    protected function handleResponseIfSuccess(Response $pendingResponse)
    {
        if (!$pendingResponse->isSuccess()) {
            return false;
        }

        // TODO parse response content string.

        if ($this->runningQueue->canAdd() && ($task = $this->dataProvider->nextTask())) {
            $request = $this->makeRequest($task);
            $this->runningQueue->add($request);
        }

        return true;
    }

    /**
     * @param Response $response
     * @param int $failCounter
     * @param int $retryCounter
     */
    protected function handleResponseIfFail(Response $response, &$failCounter, &$retryCounter)
    {
        $request  = $response->request;
        $canRetry = $this->retryQueue->add($request);
        if (!$canRetry) {
            $failCounter++;
            return;
        }

        $this->runningQueue->add($request);
        $retryCounter++;
        usleep(100000);
    }
}
