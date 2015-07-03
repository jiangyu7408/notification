<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 5:09 PM
 */

namespace Worker;

use Queue\FileQueue;

/**
 * Class EndlessTasks
 * @package Worker
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

    public function __construct($queueLocation, $batchSize = 200)
    {
        $this->queue     = new FileQueue($queueLocation);
        $this->batchSize = $batchSize;
    }

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

    protected function hasTask()
    {
        return $this->taskCounter > 0;
    }

    protected function clearTask()
    {
        $this->tasks       = [];
        $this->taskCounter = 0;
    }

    protected function addTask($task)
    {
        $this->tasks[] = $task;
        $this->taskCounter++;
    }

    protected function isTaskFull()
    {
        return $this->taskCounter === $this->batchSize;
    }
}
