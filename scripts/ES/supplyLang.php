<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 16:09.
 */
use Database\PdoFactory;
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
                $document = new \Elastica\Document($snsid, ['language' => $user['locale']]);
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
 * @param string              $gameVersion
 * @param Client              $esClient
 * @param int                 $magicNumber
 * @param Elastica\Document[] $users
 */
function batchUpdateES($gameVersion, Client $esClient, $magicNumber, array $users)
{
    $count = count($users);
    if ($count === 0) {
        return;
    }
    appendLog(sprintf('ES updater have %d user to sync', $count));

    $docGenerator = documentsGenerator($gameVersion, $magicNumber, $users);
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
        appendLog(
            sprintf(
                'Sync %d users to ES cost %s with average cost %s',
                count($documents),
                PHP_Timer::secondsToTimeString($delta),
                PHP_Timer::secondsToTimeString($delta / $count)
            )
        );
    }
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
$languageProvider = new \DataProvider\User\LocaleProvider($gameVersion, $pdoPool);
$marker = new \Facade\ShardIdMarker(CONFIG_DIR.'/log/'.$gameVersion.'/supply_locale/');
$languageProvider->setMarker($marker);

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

$batchHandler = function ($shardId, array $resultSet) use ($gameVersion, $magicNumber, $esClient) {
    static $lastShardId;
    static $shardCount;
    if ($lastShardId !== $shardId) {
        $lastShardId = $shardId;
        $shardCount = 0;
    }
    if (count($resultSet) === 0) {
        return;
    }
    $lastRound = $shardCount;
    $shardCount += count($resultSet);
    appendLog(
        sprintf(
            'walking on shard %s [No.%d => No.%d], resource cost %s',
            $shardId,
            $lastRound,
            $shardCount,
            PHP_Timer::resourceUsage()
        )
    );
    batchUpdateES($gameVersion, $esClient, $magicNumber, $resultSet);
};
$languageProvider->walk($batchHandler, $magicNumber * 10);

appendLog('%s Done', date('c'));
