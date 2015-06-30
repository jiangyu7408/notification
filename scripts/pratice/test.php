<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:29 PM
 */

use Persistency\Storage\RedisClientFactory;
use Queue\RedisQueue;

require __DIR__ . '/../vendor/autoload.php';

$redisClient = RedisClientFactory::create([
    'scheme'  => 'tcp',
    'host'    => '10.0.64.56',
    'port'    => 6379,
    'timeout' => 5.0,
]);

$queue = new RedisQueue($redisClient, 'test');

$payload = [
    'snsid'     => '675097095878591',
    'appid'     => '111111111111111',
    'timestamp' => microtime(true)
];

$msg = json_encode($payload);

$currentQueueLength = $queue->push($msg);
echo 'queue length: ' . $currentQueueLength . PHP_EOL;

while (($msg = $queue->pop())) {
    $payload = json_decode($msg, true);
    assert(is_array($payload));
    print_r($payload);
}
