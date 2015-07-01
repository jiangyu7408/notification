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

function fireNotifications($jsonArray)
{
    $requestFactory = new \Worker\Model\TaskFactory();

    $tasks = [];

    foreach ($jsonArray as $jsonString) {
        $options = json_decode($jsonString, true);
        if (!is_array($options)) {
            continue;
        }

        $tasks[] = $requestFactory->create($options[CURLOPT_URL], $options);
    }

    PHP_Timer::start();
    $worker = new \Worker\CurlWorker();
    $worker->addTasks($tasks, 2);
    $worker->run();
    $delta = PHP_Timer::stop();
    echo PHP_Timer::resourceUsage() . ' fire notif cost: ' . PHP_Timer::secondsToTimeString($delta) . PHP_EOL;
}

$queueLocation = getQueueLocation();

$taskGenerator = new EndlessTasks($queueLocation);

$bufferedNotifications = $taskGenerator->get();

/** @var string[] $tasks */
foreach ($bufferedNotifications as $tasks) {
    fireNotifications($tasks);
}
