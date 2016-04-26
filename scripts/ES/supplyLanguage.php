<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 17:58.
 */
use Database\PdoFactory;
use DataProvider\User\QueryHelperFactory;
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

$elasticaHelper = new ElasticaHelper($gameVersion, ELASTIC_SEARCH_INDEX, $magicNumber);
$elasticaHelper->setVerbose($verbose);

$localeCount = 0;
$processedCount = 0;

QueryHelperFactory::setVerbose($verbose);

$userDetailProvider = new \DataProvider\User\UserDetailProvider($gameVersion, $pdoPool);
$shardIdList = $pdoPool->listShardId();
foreach ($shardIdList as $shardId) {
    $pdo = $pdoPool->getByShardId($shardId);
    $queryHelper = QueryHelperFactory::make($pdo);

    PHP_Timer::start();
    $uidLocalePairs = $queryHelper->listLocale();
    $localeCount += count($uidLocalePairs);
    appendLog(
        sprintf(
            '%s have %d locale users cost %s',
            $shardId,
            count($uidLocalePairs),
            PHP_Timer::secondsToTimeString(PHP_Timer::stop())
        )
    );

    file_put_contents(call_user_func($logFileGetter, $gameVersion, $shardId), print_r($uidLocalePairs, true));

    $userDetailGenerator = $userDetailProvider->generate([$shardId => array_keys($uidLocalePairs)]);
    foreach ($userDetailGenerator as $payload) {
        $userInfoList = $payload['dataSet'];
        $syncCount = count($userInfoList);
        $processedCount += $syncCount;
        appendLog(
            sprintf(
                'read user info get %d users cost %s',
                $syncCount,
                PHP_Timer::resourceUsage()
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
        'Total %d locale, %d user processed, cost %s',
        $localeCount,
        $processedCount,
        PHP_Timer::resourceUsage()
    )
);
