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
$stepGenerator = WorkRoundGenerator::generate($now, $quitTimestamp, $interval, false);

$installUidProvider = new InstallUidProvider(
    $gameVersion,
    PdoFactory::makePool($gameVersion)
);

foreach ($stepGenerator as $timestamp) {
    $msg = $myself.': '.date('c', $timestamp).' run with ts '.$timestamp;
    dump($msg);
    appendLog($msg);
    $shardList = ShardHelper::listShardId($gameVersion);
    $queue = new UidQueue(UID_QUEUE_DIR, $gameVersion, $shardList);

    $date = $specifiedDate ? $specifiedDate : date('Y-m-d');
    $groupedUidList = $installUidProvider->generate($date);

    $deltaList = $installUidProvider->getDeltaList();
    array_walk(
        $deltaList,
        function ($delta, $shardId) {
            dump(sprintf('%s => %s', $shardId, PHP_Timer::secondsToTimeString($delta)));
        }
    );

    $installUser = [];
    array_map(
        function (array $uidList) use (&$installUser) {
            $installUser = array_merge($installUser, $uidList);
        },
        $groupedUidList
    );
    $data = date('c').' have '.count($installUser).PHP_EOL.print_r($installUser, true);

    $logFile = call_user_func($logFileGetter, $gameVersion, $date);
    file_put_contents($logFile, $data);
    $queue->push($groupedUidList);

    if ($specifiedDate) {
        dump(date('c'));
        dump($installUser);
        dump($myself.': quit');
        break;
    }
}
