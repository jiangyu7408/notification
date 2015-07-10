<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:23 PM.
 */

namespace Worker\Model;

/**
 * Class TaskFactory.
 */
class TaskFactory
{
    public function __construct()
    {
        $this->prototype = new Task();
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Task
     */
    public function create($url, array $options)
    {
        assert(is_string($url) && strlen($url) > 0);
        $task = clone $this->prototype;
        $task->setUrl($url)
             ->setOptions($options);

        return $task;
    }

    /**
     * @param string $url
     *
     * @return Task
     */
    public function createQuery($url)
    {
        $task = clone $this->prototype;
        $task->setUrl($url);

        return $task;
    }
}
