<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:08.
 */
use Buffer\UidQueue;
use Database\ShardHelper;
use DataProvider\User\ActiveUidProvider;
use Facade\WorkRoundGenerator;

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

$interval = isset($options['interval']) ? $options['interval'] : 20;
$backStep = isset($options['bs']) ? $options['bs'] : $interval;
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

$myself = basename(__FILE__);
$uidProvider = new ActiveUidProvider($gameVersion, \Database\PdoFactory::makePool($gameVersion));
$shardList = ShardHelper::listShardId($gameVersion);
$queue = new UidQueue(UID_QUEUE_DIR, $gameVersion, $shardList);

$stepGenerator = WorkRoundGenerator::generate($lastActiveTimestamp, $quitTimestamp, $interval, $verbose);
foreach ($stepGenerator as $timestamp) {
    $msg = $myself.': '.date('c', $timestamp).' run with ts '.date('c', $timestamp);
    appendLog($msg);

    $groupedUidList = $uidProvider->generate($lastActiveTimestamp);
    $deltaList = $uidProvider->getDeltaList();
    $totalCount = 0;
    foreach ($groupedUidList as $shardId => $shardUidList) {
        $shardCount = count($shardUidList);
        if ($shardCount === 0) {
            continue;
        }
        $verbose
        && appendLog(
            sprintf(
                '%s: %s found %d active user, cost %s',
                $myself,
                $shardId,
                $shardCount,
                PHP_Timer::secondsToTimeString($deltaList[$shardId])
            )
        );
        $totalCount += $shardCount;
    }
    appendLog(
        sprintf(
            '%s: %s found %d active user, cost %s',
            $myself,
            date('c'),
            $totalCount,
            PHP_Timer::secondsToTimeString(array_sum($deltaList))
        )
    );
    $queue->push($groupedUidList);
}
