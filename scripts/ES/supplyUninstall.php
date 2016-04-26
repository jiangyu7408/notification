<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 14:36.
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
    $filePath = LOG_DIR.'/uninstall/'.$gameVersion.'/'.$shardId;
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $filePath;
};

$pdoPool = PdoFactory::makePool($gameVersion);
$shardIdList = $pdoPool->listShardId();

$elasticaHelper = new ElasticaHelper($gameVersion, ELASTIC_SEARCH_INDEX, $magicNumber);

$uninstallCount = 0;
$processedCount = 0;
foreach ($shardIdList as $shardId) {
    $pdo = $pdoPool->getByShardId($shardId);
    $queryHelper = new QueryHelper($pdo);
    $uidSnsidPairs = $queryHelper->listUninstalledUid();
    appendLog(sprintf('%s have %d uninstalled users', $shardId, count($uidSnsidPairs)));

    file_put_contents(call_user_func($logFileGetter, $gameVersion, $shardId), print_r($uidSnsidPairs, true));

    $uninstallCount += count($uidSnsidPairs);
    $uidList = array_keys($uidSnsidPairs);
    while (($batchUidList = array_splice($uidList, 0, $magicNumber))) {
        appendLog(sprintf('%s read user info for %d users', $shardId, count($batchUidList)));

        PHP_Timer::start();
        $userInfoList = $queryHelper->readUserInfo($batchUidList, $uidSnsidPairs);
        $syncCount = count($userInfoList);
        $processedCount += $syncCount;
        appendLog(
            sprintf(
                'read user info get %d users cost %s',
                $syncCount,
                PHP_Timer::secondsToTimeString(PHP_Timer::stop())
            )
        );

        PHP_Timer::start();
        $elasticaHelper->update(
            $userInfoList,
            function ($snsidList) {
                appendLog(sprintf('Total %d user sync failed', count($snsidList)));
                appendLog('failed snsid: '.implode(',', $snsidList));
            }
        );
        appendLog(
            sprintf('Sync %d users to ES cost %s', $syncCount, PHP_Timer::secondsToTimeString(PHP_Timer::stop()))
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
