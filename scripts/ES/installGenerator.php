<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/05
 * Time: 12:59.
 */
use script\ShardHelper;
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
$interval = isset($options['interval']) ? $options['interval'] : 1800;
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

dump(date_default_timezone_get());

$stepGenerator = WorkRoundGenerator::generate($lastActiveTimestamp, $quitTimestamp, $interval, false);
foreach ($stepGenerator as $timestamp) {
    appendLog('installGenerator: '.date('c', $timestamp).' run with ts '.$timestamp);
    $shardList = ShardHelper::getShardList($gameVersion);
    $queue = new UidQueue(UID_QUEUE_DIR, $gameVersion, $shardList);

    $groupedUidList = \script\InstallGenerator::generate($gameVersion, date('Y-m-d'), $verbose);
    $installUser = [];
    array_map(
        function (array $uidList) use (&$installUser) {
            $installUser = array_merge($installUser, $uidList);
        },
        $groupedUidList
    );
    if ($verbose) {
        dump(date('c'));
        dump($installUser);
    }
    $data = date('c').' have '.count($installUser).PHP_EOL.print_r($installUser, true);
    file_put_contents(LOG_DIR.'/'.date('Ymd').'/'.$gameVersion.'.install', $data);
    $queue->push($groupedUidList);
}
