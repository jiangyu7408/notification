<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:16 PM
 */
use BusinessEntity\Notif;
use Repository\NotifRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$options = getopt('v');

$requestList = getRequest(array_key_exists('v', $options));
foreach ($requestList as $request) {
    $notif = makeNotif($request);
    if ($notif instanceof Notif) {
        registerRequest($notif);
    }
}

function makeNotif($request)
{
    $reqArr = json_decode($request, true);
    if (!is_array($reqArr)) {
        // TODO: add error handling
        return null;
    }

    if (!array_key_exists('fired', $reqArr)) {
        $reqArr['fired'] = false;
    }

    $notif = (new \BusinessEntity\NotifFactory())->make($reqArr);
    return $notif;
}

function getRequest($verbose = false)
{
    static $queue = null;

    if ($queue === null) {
        $factory             = new \Persistency\Storage\RedisFactory();
        $requestQueueSetting = [
            'scheme'  => 'tcp',
            'host'    => '10.0.64.56',
            'port'    => 6379,
            'timeout' => 5.0,
        ];

        $redis = $factory->create($requestQueueSetting);

        $queue = (new \Queue\RedisQueue($redis, 'request'))->setBlockTimeout(5);
    }

    while (true) {
        if ($verbose) {
            echo time() . ' wait for de-queue' . PHP_EOL;
        }
        $request = $queue->pop();
        if ($request) {
            yield $request;
        }
    }
}

function registerRequest(Notif $notif)
{
    static $repo = null;

    if ($repo === null) {
        $repo = (new NotifRepoBuilder())->getRepo();
    }
    $repo->register($notif);
}
