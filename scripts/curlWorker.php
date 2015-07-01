<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 12:26 PM
 */
require __DIR__ . '/../bootstrap.php';

$worker = new \Worker\CurlWorker();
$worker->addTasks([]);
$worker->run();
