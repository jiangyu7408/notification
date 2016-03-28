<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:18 PM.
 */
namespace Worker\Model;

/**
 * Class TaskContainer.
 */
class TaskContainer
{
    /**
     * @var Task[]
     */
    protected $container = [];

    /**
     * @param Task $task
     */
    public function add(Task $task)
    {
        $this->container[$task->uniqueId()] = $task;
    }

    /**
     * @param Task $query
     *
     * @return Task
     */
    public function get(Task $query)
    {
        return $this->container[$query->uniqueId()];
    }

    /**
     * @param Task $task
     */
    public function remove(Task $task)
    {
        unset($this->container[$task->uniqueId()]);
    }
}
