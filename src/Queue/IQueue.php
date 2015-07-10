<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 10:24 AM.
 */

namespace Queue;

/**
 * Class RedisQueue.
 */
interface IQueue
{
    /**
     * @param string $msg
     *
     * @return int
     */
    public function push($msg);

    /**
     * @return string
     */
    public function pop();
}
