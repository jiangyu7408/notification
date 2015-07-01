<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 3:37 PM
 */

require __DIR__ . '/../bootstrap.php';

class EndlessTasks
{
    /**
     * @var \Queue\FileQueue
     */
    protected $queue;
    /**
     * @var array
     */
    protected $tasks = [];
    protected $taskCounter = 0;

    public function __construct($queueLocation, $batchSize = 200)
    {
        $this->queue     = new \Queue\FileQueue($queueLocation);
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

function fireNotifications($tasks)
{
    $requests = array_map(function ($task) {
        return json_decode($task, true);
    }, $tasks);

    print_r($requests);
}

$queueLocation = getQueueLocation();

$taskGenerator = new EndlessTasks($queueLocation);

$bufferedNotifications = $taskGenerator->get();

/** @var string[] $tasks */
foreach ($bufferedNotifications as $tasks) {
    fireNotifications($tasks);
}
