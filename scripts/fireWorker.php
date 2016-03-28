<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 3:37 PM.
 */
use Worker\EndlessTasks;

require __DIR__.'/../bootstrap.php';

$fireNotifications = function ($jsonArray) {
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
    $worker->addTasks($tasks);
    $worker->run();
    $delta = PHP_Timer::stop();
    echo PHP_Timer::resourceUsage().' fire notif cost: '.PHP_Timer::secondsToTimeString($delta).PHP_EOL;
};

$facebookOptions = \Application\Facade::getInstance()->getFBGatewayOptions();
dump($facebookOptions);

$taskGenerator = new EndlessTasks($facebookOptions['queueLocation']);

$bufferedNotifications = $taskGenerator->get();

/** @var string[] $tasks */
foreach ($bufferedNotifications as $tasks) {
    call_user_func($fireNotifications, $tasks);
}
