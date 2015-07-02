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

$options = getopt('vn:', ['fireTime:']);
$verbose   = array_key_exists('v', $options);

$requestQueueSetting = \Application\Facade::getInstance()->getParam('redisQueue');
$queueName           = $requestQueueSetting['queueName'];
if ($verbose) {
    echo "Queue[$queueName] DSN: " . implode('/', $requestQueueSetting) . PHP_EOL;
}

$redis = (new RedisClientFactory())->create($requestQueueSetting);
$queue = new RedisQueue($redis, $queueName);

$fixture = require __DIR__ . '/../tests/_fixture/fb.php';
$config  = $fixture['good'];

$appid     = $config['appId'];
$snsid     = $config['snsid'];
$secretKey = $config['secretKey'];
$endpoint  = $config['openGraphEndpoint'];

$cnt = isset($options['n']) ? abs($options['n']) : 3;
for ($i = 0; $i < $cnt; $i++) {
    $msg = [
        'appid'    => $appid,
        'snsid'    => $snsid,
        'feature'  => 'debug',
        'template' => 'This is a notif test message at ' . date('His'),
        'trackRef' => 'track_' . microtime(true),
        'fireTime' => isset($options['fireTime']) ? (int)$options['fireTime'] : time() + 10,
    ];

    $queueLength = $queue->push(json_encode($msg));
    echo 'current request queue length = ' . $queueLength . PHP_EOL;
}
