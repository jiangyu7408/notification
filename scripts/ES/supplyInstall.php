<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 14:07.
 */
use Database\PdoFactory;
use Database\ShardHelper;
use DataProvider\User\InstallUidProvider;
use Facade\ES\Indexer;
use Facade\ES\IndexerFactory;

require __DIR__.'/../../bootstrap.php';

$batchUpdateES = function (Indexer $indexer, array $users) {
    $count = count($users);
    if ($count === 0) {
        return;
    }
    appendLog(sprintf('ES updater have %d user to sync', $count));
    $deltaList = $indexer->batchUpdate($users, function ($userCount, $delta) {
        appendLog(sprintf('on ES batch update of %d users cost %s', $userCount, PHP_Timer::secondsToTimeString($delta)));
    });
    $totalDelta = array_sum($deltaList);
    appendLog(
        sprintf(
            'Sync %d users to ES cost %s with average cost %s',
            $count,
            PHP_Timer::secondsToTimeString($totalDelta),
            PHP_Timer::secondsToTimeString($totalDelta / $count)
        )
    );
};

$options = getopt('v', ['reset', 'gv:', 'from:', 'to:', 'magic:']);
$verbose = isset($options['v']);
$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$markerLocation = sprintf('%s/log/%s/marker', CONFIG_DIR, $gameVersion);
appendLog($markerLocation);
if (isset($options['reset'])) {
    $backup = sprintf('%s.%s', $markerLocation, date('Ymd'));
    rename($markerLocation, $backup);
    appendLog($backup);

    return;
}
$calendarMarker = new \Facade\CalendarDayMarker($markerLocation);

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

$pdoPool = PdoFactory::makePool($gameVersion);
$installUidProvider = new InstallUidProvider($gameVersion, $pdoPool);
$userDetailProvider = new \DataProvider\User\UserDetailProvider($gameVersion, $pdoPool);

$magicNumber = isset($options['magic']) ? (int) $options['magic'] : 500;
assert($magicNumber > 10);
$indexer = IndexerFactory::make(ELASTIC_SEARCH_HOST, $gameVersion, $magicNumber);

$shardList = ShardHelper::listShardId($gameVersion);

$totalUser = 0;
foreach ($stepGenerator as $date) {
    $markerDate = new DateTimeImmutable($date);
    if ($calendarMarker->isMarked($markerDate)) {
        appendLog($date.' bypassed');
        continue;
    }
    $msg = basename(__FILE__).': process for '.$date.' run with ts '.time();
    appendLog($msg);

    $groupedUidList = $installUidProvider->generate($date, function ($shardId, $userCount, $delta) {
        if ($userCount === 0) {
            return;
        }
        appendLog(sprintf('%s install(%d) cost %s', $shardId, $userCount, PHP_Timer::secondsToTimeString($delta)));
    });

    $installUser = [];
    array_map(
        function (array $uidList) use (&$installUser) {
            $installUser = array_merge($installUser, $uidList);
        },
        $groupedUidList
    );
    $newInstallCount = count($installUser);
    appendLog(sprintf('Total %d new install on %s', $newInstallCount, $date));

    $totalUser += $newInstallCount;

    $groupedDetail = $userDetailProvider->generate($groupedUidList);

    $esUpdateQueue = [];
    foreach ($groupedDetail as $shardId => $shardUserList) {
        $count = count($shardUserList);
        if ($count === 0) {
            continue;
        }
        appendLog(sprintf('%s have %d user to sync', $shardId, $count));
        $esUpdateQueue = array_merge($esUpdateQueue, $shardUserList);
        $queueLength = count($esUpdateQueue);
        if ($queueLength >= $magicNumber) {
            appendLog(
                sprintf(
                    '%s: flush ES update queue: %d user to sync %s',
                    date('c'),
                    $queueLength,
                    PHP_Timer::resourceUsage()
                )
            );
            call_user_func($batchUpdateES, $indexer, $esUpdateQueue);
            $esUpdateQueue = [];
        }
    }
    call_user_func($batchUpdateES, $indexer, $esUpdateQueue);

    $calendarMarker->mark($markerDate);

    appendLog(sprintf('Total %d user processed, cost %s', $totalUser, PHP_Timer::resourceUsage()));
}
