<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 14:07.
 */
use Buffer\UidQueue;
use Database\PdoFactory;
use Database\ShardHelper;
use DataProvider\User\InstallUidProvider;

require __DIR__.'/../../bootstrap.php';

$options = getopt('v', ['gv:', 'from:', 'to:']);
$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$fromDay = isset($options['from']) ? $options['from'] : date('Ymd');
$toDay = isset($options['to']) ? $options['to'] : date('Ymd');

$logFileGetter = function ($gameVersion, $date) {
    $logDate = str_replace('-', '', $date);

    $filePath = LOG_DIR.'/'.$logDate.'/'.$gameVersion.'.supply';
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $filePath;
};

$fromDate = date_create_from_format('Ymd', $fromDay);
$toDate = date_create_from_format('Ymd', $toDay);
$stepGenerator = \Facade\CalendarDayGenerator::generate($fromDate->getTimestamp(), $toDate->getTimestamp());

$installUidProvider = new InstallUidProvider($gameVersion, PdoFactory::makePool($gameVersion));

$shardList = ShardHelper::listShardId($gameVersion);
$queue = new UidQueue(UID_QUEUE_DIR, $gameVersion, $shardList);

foreach ($stepGenerator as $date) {
    $msg = basename(__FILE__).': process for '.$date.' run with ts '.time();
    appendLog($msg);

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
}
