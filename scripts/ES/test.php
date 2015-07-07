<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:29 AM
 */

require __DIR__ . '/../../vendor/autoload.php';

$platform = new \Environment\Platform(__DIR__ . '/../../../farm-server-conf/');

$shardList = $platform->getMySQLShards('tw');

foreach ($shardList as $shard) {
    dump($shard);
}
