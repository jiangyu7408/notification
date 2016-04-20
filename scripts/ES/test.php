<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:29 AM
 */

require __DIR__.'/../../bootstrap.php';

$options = getopt('', ['gv:']);

$gameVersion = isset($options['gv']) ? $options['gv'] : 'tw';
appendLog('game version: '.$gameVersion);

$shardList = \Environment\PlatformFactory::make($gameVersion)->getMySQLShards();

foreach ($shardList as $shard) {
    appendLog($shard);
}
