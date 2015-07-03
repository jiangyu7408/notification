<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 12:00 PM
 */

use Persistency\Storage\RedisClientFactory;
use Queue\RedisQueue;

require __DIR__ . '/../bootstrap.php';

$options = getopt('vn:', ['fireTime:', 'snsid:']);
$verbose = array_key_exists('v', $options);

$requestQueueConfig = \Application\Facade::getInstance()->getRegisterQueueConfig();

$queueName = $requestQueueConfig->queueName;
if ($verbose) {
    dump("Queue[$queueName] DSN: " . $requestQueueConfig->toString());
}

$registerEntry = new RedisQueue(
    (new RedisClientFactory())->create($requestQueueConfig),
    $queueName
);

$snsid    = isset($options['snsid']) ? trim($options['snsid']) : '100001349218797';
$fireTime = isset($options['fireTime']) ? (int)$options['fireTime'] : time() + 3;
dump('snsid: ' . $snsid . ', fireTime: ' . date('Y-m-d H:i:s', $fireTime));

$facebookOptions = \Application\Facade::getInstance()->getParam('facebook');

$cnt = isset($options['n']) ? abs($options['n']) : 3;
for ($i = 0; $i < $cnt; $i++) {
    $msg = [
        'appid'    => $facebookOptions['appId'],
        'snsid'    => $snsid,
        'feature'  => 'debug', // TODO route to correct appid
        'template' => 'This is a notif test message at ' . date('H:i:s', $fireTime),
        'trackRef' => 'track_' . microtime(true),
        'fireTime' => $fireTime,
    ];

    $queueLength = $registerEntry->push(json_encode($msg));
    dump('current request queue length = ' . $queueLength);
}
