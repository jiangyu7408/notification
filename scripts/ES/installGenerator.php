<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/05
 * Time: 12:59.
 */
use Buffer\UidQueue;
use Database\PdoFactory;
use Database\ShardHelper;
use DataProvider\User\InstallUidProvider;
use Facade\WorkRoundGenerator;

require __DIR__.'/../../bootstrap.php';

$options = getopt('v', ['gv:', 'date:', 'interval:', 'round:']);

$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$specifiedDate = isset($options['date']) ? $options['date'] : '';
$interval = isset($options['interval']) ? $options['interval'] : 1800;
$round = isset($options['round']) ? $options['round'] : 100;

$now = time();
$quitTimestamp = $now + $round * $interval;

if ($verbose) {
    $msg = sprintf(
        'game version: %s, specifiedDate=%s, interval=%d, round=%d, start at: %s, quit at: %s',
        $gameVersion,
        $specifiedDate,
        $interval,
        $round,
        date('H:i:s'),
        date('H:i:s', $quitTimestamp)
    );
    dump($msg);
}

$logFileGetter = function ($gameVersion, $date) {
    $logDate = str_replace('-', '', $date);

    $filePath = LOG_DIR.'/'.$logDate.'/'.$gameVersion.'.install';
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $filePath;
};

$myself = basename(__FILE__);

$installUidProvider = new InstallUidProvider($gameVersion, PdoFactory::makePool($gameVersion));

$shardList = ShardHelper::listShardId($gameVersion);
$queue = new UidQueue(UID_QUEUE_DIR, $gameVersion, $shardList);

$stepGenerator = WorkRoundGenerator::generate($now, $quitTimestamp, $interval, false);
foreach ($stepGenerator as $timestamp) {
    $msg = $myself.': '.date('c', $timestamp).' run with ts '.date('c', $timestamp);
    appendLog($msg);

    $date = $specifiedDate ? $specifiedDate : date('Y-m-d');
    $groupedUidList = $installUidProvider->generate(
        $date,
        function ($shardId, $userCount, $delta) {
            if ($userCount === 0) {
                return;
            }
            appendLog(sprintf('%s install(%d) cost %s', $shardId, $userCount, PHP_Timer::secondsToTimeString($delta)));
        }
    );
    $queue->push($groupedUidList);
    $deltaList = $installUidProvider->getDeltaList();

    $totalCount = 0;
    foreach ($groupedUidList as $shardId => $shardUidList) {
        $shardCount = count($shardUidList);
        if ($shardCount === 0) {
            continue;
        }
        $totalCount += $shardCount;
    }
    appendLog(
        sprintf(
            '%s: %s found %d install user, cost %s',
            $myself,
            date('c'),
            $totalCount,
            PHP_Timer::secondsToTimeString(array_sum($deltaList))
        )
    );

    $logFile = call_user_func($logFileGetter, $gameVersion, $date);
    file_put_contents($logFile, date('c').' have '.$totalCount.PHP_EOL.print_r($groupedUidList, true));

    if ($specifiedDate) {
        dump(date('c'));
        dump($groupedUidList);
        dump($myself.': quit');
        break;
    }
}
