<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:32 PM.
 */

namespace Worker\Queue;

use Worker\Model\Task;

/**
 * Class TaskProvider.
 */
class TaskProvider extends \SplQueue
{
    /**
     * @param Task[] $tasks
     */
    public function __construct(array $tasks)
    {
        foreach ($tasks as $task) {
            $this->push($task);
        }
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * @return Task|null
     */
    public function nextTask()
    {
        try {
            return $this->pop();
        } catch (\RuntimeException $e) {
            return;
        }
    }
}
