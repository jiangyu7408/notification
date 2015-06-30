<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 12:00 PM
 */

require __DIR__ . '/../vendor/autoload.php';

$options   = getopt('v', ['fireTime:']);
$verbose   = array_key_exists('v', $options);
$queueName = 'request';

$factory = new \Persistency\Storage\RedisFactory();

$requestQueueSetting = [
    'scheme'  => 'tcp',
    'host'    => '10.0.64.56',
    'port'    => 6379,
    'timeout' => 5.0,
];
if ($verbose) {
    echo "Queue[$queueName] DSN: " . implode('/', $requestQueueSetting) . PHP_EOL;
}

$redis = $factory->create($requestQueueSetting);

$msg = [
    'appid'    => 'appid',
    'snsid'    => '675097095878591',
    'feature'  => 'debug',
    'template' => 'This is a notif test message',
    'trackRef' => 'debug',
    'fireTime' => isset($options['fireTime']) ? (int)$options['fireTime'] : time() + 10,
];

$queue       = new \Queue\RedisQueue($redis, $queueName);
$queueLength = $queue->push(json_encode($msg));
echo 'current request queue length = ' . $queueLength . PHP_EOL;
