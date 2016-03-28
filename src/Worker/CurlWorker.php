<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 11:51 AM.
 */

namespace Worker;

use Worker\Model\RequestFactory;
use Worker\Model\Response;
use Worker\Model\Task;
use Worker\Queue\RetryQueue;
use Worker\Queue\RunningQueue;
use Worker\Queue\TaskProvider;

/**
 * Class CurlWorker.
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
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * CurlWorker constructor.
     */
    public function __construct()
    {
        $this->requestFactory = new RequestFactory();
    }

    /**
     * @param Task[] $tasks
     * @param int    $concurrency
     *
     * @return bool
     */
    public function addTasks(array $tasks, $concurrency = 10)
    {
        if (($this->dataProvider instanceof TaskProvider) && (!$this->dataProvider->isEmpty())) {
            return false;
        }

        $this->dataProvider = new TaskProvider($tasks);
        $this->retryQueue = new RetryQueue();

        $this->runningQueue = new RunningQueue($this->dataProvider, $concurrency);
        while ($this->runningQueue->canAdd()
               && ($task = $this->dataProvider->nextTask())) {
            $request = $this->makeRequest($task);
            $this->runningQueue->add($request);
        }

        return true;
    }

    /**
     *
     */
    public function run()
    {
        $retryCounter = 0;
        $failCounter = 0;
        $successCounter = 0;

        $pendingResponseList = $this->runningQueue->run();
        /** @var Response $pendingResponse */
        foreach ($pendingResponseList as $pendingResponse) {
            echo '*';
            $success = $this->handleResponseIfSuccess($pendingResponse);
            if ($success) {
                ++$successCounter;
                continue;
            }

            $this->handleResponseIfFail($pendingResponse, $failCounter, $retryCounter);
        }

        echo PHP_EOL."success: {$successCounter}, retry: {$retryCounter}, fail: {$failCounter}".PHP_EOL;
    }

    /**
     * @param Task $task
     *
     * @return Model\Request
     */
    protected function makeRequest(Task $task)
    {
        return $this->requestFactory->create($task->getOptions());
    }

    /**
     * @param Response $pendingResponse
     *
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
     * @param int      $failCounter
     * @param int      $retryCounter
     */
    protected function handleResponseIfFail(Response $response, &$failCounter, &$retryCounter)
    {
        $request = $response->request;
        $canRetry = $this->retryQueue->add($request);
        if (!$canRetry) {
            ++$failCounter;

            return;
        }

        $this->runningQueue->add($request);
        ++$retryCounter;
        usleep(100000);
    }
}
