<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 14:07.
 */
use Database\PdoFactory;
use DataProvider\User\InstallUidProvider;
use DataProvider\User\UserDetailProvider;
use Facade\CalendarDayGenerator;
use Facade\ES\Indexer;
use Facade\ES\IndexerFactory;

require __DIR__.'/../../bootstrap.php';

$esUpdateHandler = function (Indexer $indexer, array $users) {
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

$options = getopt(
    'v',
    [
        'reset',
        'gv:',
        'from:',
        'to:',
        'magic:',
        'safe:',
    ]
);
$verbose = isset($options['v']);

$safeRound = getenv('SAFE');
if (!$safeRound) {
    $safeRound = isset($options['safe']) ? (int) $options['safe'] : 0;
}
if ($safeRound < 1) {
    $safeRound = 30;
}

$gameVersion = getenv('GV');
if (!$gameVersion) {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$markerLocation = sprintf('%s/log/marker_%d/%s', CONFIG_DIR, ELASTIC_SEARCH_SCHEMA_VERSION, $gameVersion);
if (isset($options['reset'])) {
    $backup = sprintf('%s.%s', $markerLocation, date('Ymd'));
    rename($markerLocation, $backup);
    appendLog($backup);

    return;
}
$calendarMarker = new \Facade\CalendarDayMarker($markerLocation);

$fromDay = getenv('FROM');
if (!$fromDay) {
    $fromDay = isset($options['from']) ? $options['from'] : date('Ymd');
}
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
if (!($fromDate instanceof \DateTime)) {
    appendLog(sprintf('from [%s] not valid', $fromDay));

    return;
}
$toDate = date_create_from_format('Ymd', $toDay);
if (!($toDate instanceof \DateTime)) {
    appendLog(sprintf('to [%s] not valid', $toDay));

    return;
}

$pdoPool = PdoFactory::makePool($gameVersion);
$installUidProvider = new InstallUidProvider($gameVersion, $pdoPool);
$userDetailProvider = new UserDetailProvider($gameVersion, $pdoPool);

$magicNumber = isset($options['magic']) ? (int) $options['magic'] : 500;
assert($magicNumber > 10);
$indexer = IndexerFactory::make(ELASTIC_SEARCH_HOST, $gameVersion, $magicNumber);

if ($verbose) {
    dump(
        sprintf(
            'version: %s, from: %s, to: %s, safe: %d, magic: %d',
            $gameVersion,
            $fromDay,
            $toDay,
            $safeRound,
            $magicNumber
        )
    );
}

$calendarDayGenerator = CalendarDayGenerator::generate($fromDate->getTimestamp(), $toDate->getTimestamp());
$totalUser = 0;
$processedRound = 0;
foreach ($calendarDayGenerator as $calendarDay) {
    $markerDate = new DateTimeImmutable($calendarDay);
    if ($calendarMarker->isMarked($markerDate)) {
        continue;
    }
    $msg = basename(__FILE__).': process for '.$calendarDay.' run with ts '.date('c');
    appendLog($msg);

    $groupedUidList = $installUidProvider->generate(
        $calendarDay,
        function ($shardId, $userCount, $delta) {
            if ($userCount === 0) {
                return;
            }
            appendLog(sprintf('%s install(%d) cost %s', $shardId, $userCount, PHP_Timer::secondsToTimeString($delta)));
        }
    );

    $distribution = array_map(function (array $uidList) {
        return count($uidList);
    }, $groupedUidList);
    $newInstallCount = array_sum($distribution);
    appendLog(sprintf('Total %d new install on %s', $newInstallCount, $calendarDay));

    $totalUser += $newInstallCount;

    $start = microtime(true);
    $groupedDetail = $userDetailProvider->generate($groupedUidList);
    $delta = microtime(true) - $start;
    appendLog(
        sprintf(
            'Total %d new install on %s, read detail cost %s',
            $newInstallCount,
            $calendarDay,
            PHP_Timer::secondsToTimeString($delta)
        )
    );

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
                    '%s: flush ES update queue: %d user on date %s to sync %s',
                    date('c'),
                    $queueLength,
                    $calendarDay,
                    PHP_Timer::resourceUsage()
                )
            );
            call_user_func($esUpdateHandler, $indexer, $esUpdateQueue);
            $esUpdateQueue = [];
        }
    }
    call_user_func($esUpdateHandler, $indexer, $esUpdateQueue);

    $calendarMarker->mark($markerDate);

    appendLog(sprintf('Total %d user processed, cost %s', $totalUser, PHP_Timer::resourceUsage()));
    ++$processedRound;
    if ($processedRound >= $safeRound) {
        appendLog(sprintf('Safe round finished, cost %s', PHP_Timer::resourceUsage()));
        break;
    }
}
