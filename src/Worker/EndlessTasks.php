<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 5:09 PM.
 */
namespace Worker;

use Queue\FileQueue;

/**
 * Class EndlessTasks.
 */
class EndlessTasks
{
    /**
     * @var FileQueue
     */
    protected $queue;
    /**
     * @var array
     */
    protected $tasks = [];
    protected $taskCounter = 0;

    /**
     * EndlessTasks constructor.
     *
     * @param string $queueLocation
     * @param int    $batchSize
     */
    public function __construct($queueLocation, $batchSize = 200)
    {
        $this->queue = new FileQueue($queueLocation);
        $this->batchSize = $batchSize;
    }

    /**
     * @return \Generator
     */
    public function get()
    {
        while (true) {
            $paramInJson = $this->queue->pop();
            if ($paramInJson === '') {
                if ($this->hasTask()) {
                    yield $this->tasks;
                    $this->clearTask();
                }
                sleep(1);
                continue;
            }

            $this->addTask($paramInJson);

            if ($this->isTaskFull()) {
                yield $this->tasks;
                $this->clearTask();
            }
        }
    }

    /**
     * @return bool
     */
    protected function hasTask()
    {
        return $this->taskCounter > 0;
    }

    protected function clearTask()
    {
        $this->tasks = [];
        $this->taskCounter = 0;
    }

    /**
     * @param mixed $task
     */
    protected function addTask($task)
    {
        $this->tasks[] = $task;
        ++$this->taskCounter;
    }

    /**
     * @return bool
     */
    protected function isTaskFull()
    {
        return $this->taskCounter >= $this->batchSize;
    }
}
