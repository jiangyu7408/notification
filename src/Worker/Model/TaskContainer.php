<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:18 PM
 */

namespace Worker\Model;

/**
 * Class TaskContainer
 * @package Worker\Task
 */
class TaskContainer
{
    /**
     * @var Task[]
     */
    protected $container = [];

    public function add(Task $task)
    {
        $this->container[$task->uniqueId()] = $task;
    }

    public function get(Task $query)
    {
        return $this->container[$query->uniqueId()];
    }

    public function remove(Task $task)
    {
        unset($this->container[$task->uniqueId()]);
    }
}
