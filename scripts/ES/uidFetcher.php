<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:08.
 */
use script\ShardHelper;
use script\UidListGenerator;
use script\UidQueue;
use script\WorkRoundGenerator;

require __DIR__.'/../../bootstrap.php';

spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

$options = getopt('v', ['gv:', 'es:', 'bs:', 'interval:', 'round:', 'pop']);

$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$backStep = isset($options['bs']) ? $options['bs'] : 1;
$interval = isset($options['interval']) ? $options['interval'] : 20;
$round = isset($options['round']) ? $options['round'] : 100;

$lastActiveTimestamp = time() - $backStep;
$quitTimestamp = time() + $round * $interval;

if ($verbose) {
    $msg = sprintf(
        'game version: %s, backStep=%d, interval=%d, round=%d, start at: %s, quit at: %s',
        $gameVersion,
        $backStep,
        $interval,
        $round,
        date('H:i:s', $lastActiveTimestamp),
        date('H:i:s', $quitTimestamp)
    );
    dump($msg);
}

$stepGenerator = WorkRoundGenerator::generate($lastActiveTimestamp, $quitTimestamp, $interval, $verbose);
foreach ($stepGenerator as $timestamp) {
    dump(date('Y-m-d H:i:s'));
    appendLog(date('c', $timestamp).' run with ts '.$timestamp);
    $shardList = ShardHelper::getShardList($gameVersion);
    $queue = new UidQueue(UID_QUEUE_DIR, $shardList);

    $groupedUidList = UidListGenerator::generate($gameVersion, $lastActiveTimestamp);
    $verbose && dump($groupedUidList);
    $queue->push($groupedUidList);
}
