<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/12/06
 * Time: 21:16.
 */
use Daemon\Sharding\PollMySQLOnSingleMachine;
use Daemon\Sharding\Resource\ResourceCollection;

require __DIR__.'/../vendor/autoload.php';

$allShardSetting = [
    'db1' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'hello',
        'password' => 'world',
        'database' => 'db1',
    ],
    'db2' => [
        'host' => '127.0.0.1',
        'port' => '3307',
        'username' => 'hello',
        'password' => 'world',
        'database' => 'db2',
    ],
    'db3' => [
        'host' => '127.0.0.1',
        'port' => '3308',
        'username' => 'hello',
        'password' => 'world',
        'database' => 'db3',
    ],
];

$poll = new PollMySQLOnSingleMachine($allShardSetting, function (ResourceCollection $collection) {
    foreach ($collection as $shardConfig) {
        dump($shardConfig->getUniqueIdentity());
        sleep(3);
    }
});
$poll->setWorkerCount(2);
$poll->poll();
