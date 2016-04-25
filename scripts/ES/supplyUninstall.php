<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 14:36.
 */
use Database\PdoFactory;
use DataProvider\User\UninstallUidProvider;
use DataProvider\User\UserDetailProvider;
use Elastica\Client;
use Elastica\Type;

require __DIR__.'/../../bootstrap.php';

/**
 * @param string $gameVersion
 * @param int    $magicNumber
 * @param array  $users
 *
 * @return Generator
 */
function documentsGenerator($gameVersion, $magicNumber, array $users)
{
    while (($batch = array_splice($users, 0, $magicNumber))) {
        $documents = array_map(
            function (array $user) use ($gameVersion) {
                $snsid = $user['snsid'];
                $document = new \Elastica\Document($snsid, ['status' => 0]);
                $document->setDocAsUpsert(true)
                         ->setIndex(ELASTIC_SEARCH_INDEX)
                         ->setType('user:'.$gameVersion);

                return $document;
            },
            $batch
        );
        yield $documents;
    }
}

/**
 * @param string $gameVersion
 * @param Client $esClient
 * @param int    $magicNumber
 * @param array  $users
 */
function batchUpdateES($gameVersion, Client $esClient, $magicNumber, array $users)
{
    $count = count($users);
    if ($count === 0) {
        return;
    }
    appendLog(sprintf('ES updater have %d user to sync', $count));

    $docGenerator = documentsGenerator($gameVersion, $magicNumber, $users);
    $totalDelta = 0;
    foreach ($docGenerator as $documents) {
        $start = microtime(true);
        $responseSet = $esClient->updateDocuments($documents);
        foreach ($responseSet as $response) {
            if (!$response->isOk()) {
                $metaData = $response->getAction()->getMetadata();
                $snsid = $metaData['_id'];
                appendLog(sprintf('SyncFail: update doc on snsid %s failed', $snsid));
            }
        }
        $delta = microtime(true) - $start;
        $totalDelta += $delta;
        appendLog(
            sprintf(
                'Sync %d users to ES cost %s with average cost %s',
                count($documents),
                PHP_Timer::secondsToTimeString($delta),
                PHP_Timer::secondsToTimeString($delta / $count)
            )
        );
    }

    appendLog(
        sprintf(
            'Sync %d users to ES cost %s with average cost %s',
            $count,
            PHP_Timer::secondsToTimeString($totalDelta),
            PHP_Timer::secondsToTimeString($totalDelta / $count)
        )
    );
}

;

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

$logFileGetter = function ($gameVersion) {
    $filePath = LOG_DIR.'/'.$gameVersion.'.uninstall';
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $filePath;
};

$pdoPool = PdoFactory::makePool($gameVersion);
$uninstallUidProvider = new UninstallUidProvider($gameVersion, $pdoPool);
$userDetailProvider = new UserDetailProvider($gameVersion, $pdoPool);

$esClient = new \Elastica\Client(['host' => ELASTIC_SEARCH_HOST, 'port' => ELASTIC_SEARCH_PORT]);

if ($verbose) {
    appendLog(
        sprintf(
            'version: %s, magic: %d',
            $gameVersion,
            $magicNumber
        )
    );
}

$groupedUidList = $uninstallUidProvider->generate(
    function ($shardId, $userCount, $delta) {
        if ($userCount === 0) {
            return;
        }
        appendLog(sprintf('%s uninstall(%d) cost %s', $shardId, $userCount, PHP_Timer::secondsToTimeString($delta)));
    }
);

$distribution = array_map(function (array $uidList) {
    return count($uidList);
}, $groupedUidList);
$uninstallCount = array_sum($distribution);
appendLog(sprintf('Total %d uninstall', $uninstallCount));

foreach ($groupedUidList as $shardId => $shardUidList) {
    if (count($shardUidList) === 0) {
        continue;
    }
    $detail = $userDetailProvider->generate([$shardId => $shardUidList]);
    batchUpdateES($gameVersion, $esClient, $magicNumber, $detail[$shardId]);
}

appendLog(sprintf('Total %d user processed, cost %s', $uninstallCount, PHP_Timer::resourceUsage()));
