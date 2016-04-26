<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 17:58.
 */
use Database\PdoFactory;
use DataProvider\User\QueryHelper;
use Elastica\Type;
use Facade\ElasticSearch\ElasticaHelper;

require __DIR__.'/../../bootstrap.php';

$options = getopt(
    'v',
    [
        'gv:',
        'magic:',
    ]
);
$verbose = isset($options['v']);
assert(isset($options['gv']), 'game version not defined');
$gameVersion = trim($options['gv']);
$magicNumber = isset($options['magic']) ? (int) $options['magic'] : 500;
assert($magicNumber > 0);

if ($verbose) {
    appendLog(
        sprintf(
            'version: %s, magic: %d',
            $gameVersion,
            $magicNumber
        )
    );
}

$logFileGetter = function ($gameVersion, $shardId) {
    $filePath = LOG_DIR.'/language/'.$gameVersion.'/'.$shardId;
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $filePath;
};

$pdoPool = PdoFactory::makePool($gameVersion);
$shardIdList = $pdoPool->listShardId();

$elasticaHelper = new ElasticaHelper($gameVersion, ELASTIC_SEARCH_INDEX, $magicNumber);
$elasticaHelper->setVerbose($verbose);

$uninstallCount = 0;
$processedCount = 0;
foreach ($shardIdList as $shardId) {
    $pdo = $pdoPool->getByShardId($shardId);
    $queryHelper = new QueryHelper($pdo, $verbose);
    $uninstalledUidSnsidPairs = $queryHelper->listUninstalledUid();

    PHP_Timer::start();
    $uidLocalePairs = $queryHelper->listLocale();
    appendLog(
        sprintf(
            '%s have %d uninstalled users cost %s',
            $shardId,
            count($uidLocalePairs),
            PHP_Timer::secondsToTimeString(PHP_Timer::stop())
        )
    );

    file_put_contents(call_user_func($logFileGetter, $gameVersion, $shardId), print_r($uidLocalePairs, true));

    $uninstallCount += count($uidLocalePairs);
    $uidList = array_keys($uidLocalePairs);
    while (($batchUidList = array_splice($uidList, 0, $magicNumber))) {
        appendLog(sprintf('%s read user info for %d users', $shardId, count($batchUidList)));

        PHP_Timer::start();
        $userInfoList = $queryHelper->readUserInfo($batchUidList, $uninstalledUidSnsidPairs);
        $syncCount = count($userInfoList);
        $processedCount += $syncCount;
        appendLog(
            sprintf(
                'read user info get %d users cost %s, %s',
                $syncCount,
                PHP_Timer::secondsToTimeString(PHP_Timer::stop()),
                PHP_Timer::resourceUsage()
            )
        );

        array_walk($userInfoList, function (array &$userInfo) use ($uidLocalePairs) {
            $uid = $userInfo['uid'];
            $userInfo['language'] = $uidLocalePairs[$uid];
        });

        PHP_Timer::start();
        $elasticaHelper->update(
            $userInfoList,
            function ($snsidList) {
                appendLog(sprintf('Total %d user sync failed', count($snsidList)));
                appendLog('failed snsid: '.implode(',', $snsidList));
            }
        );
        appendLog(
            sprintf(
                'Sync %d users to ES cost %s, %s',
                $syncCount,
                PHP_Timer::secondsToTimeString(PHP_Timer::stop()),
                PHP_Timer::resourceUsage()
            )
        );
    }
}

appendLog(
    sprintf(
        'Total %d uninstall, %d user processed, cost %s',
        $uninstallCount,
        $processedCount,
        PHP_Timer::resourceUsage()
    )
);
