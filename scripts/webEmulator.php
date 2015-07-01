<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 12:00 PM
 */

use Persistency\Storage\RedisClientFactory;
use Queue\RedisQueue;

require __DIR__ . '/../vendor/autoload.php';

$options = getopt('vn:', ['fireTime:']);
$verbose   = array_key_exists('v', $options);
$queueName = 'request';

$requestQueueSetting = [
    'scheme'  => 'tcp',
    'host'    => '10.0.64.56',
    'port'    => 6379,
    'timeout' => 5.0,
];
if ($verbose) {
    echo "Queue[$queueName] DSN: " . implode('/', $requestQueueSetting) . PHP_EOL;
}

$redis = (new RedisClientFactory())->create($requestQueueSetting);
$queue = new RedisQueue($redis, $queueName);

$cnt = isset($options['n']) ? abs($options['n']) : 3;
for ($i = 0; $i < $cnt; $i++) {
    $msg = [
        'appid'    => 'appid',
        'snsid'    => '675097095878591',
        'feature'  => 'debug',
        'template' => 'This is a notif test message',
        'trackRef' => 'track_' . microtime(true),
        'fireTime' => isset($options['fireTime']) ? (int)$options['fireTime'] : time() + 10,
    ];

    $queueLength = $queue->push(json_encode($msg));
    echo 'current request queue length = ' . $queueLength . PHP_EOL;
}
