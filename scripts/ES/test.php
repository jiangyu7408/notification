<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:29 AM
 */

require __DIR__ . '/../../bootstrap.php';

$base = __DIR__ . '/../../../farm-server-conf/';
assert(is_dir($base));

$platform = new \Environment\Platform($base);

$shardList = $platform->getMySQLShards('tw');

foreach ($shardList as $shard) {
    dump($shard);
}
