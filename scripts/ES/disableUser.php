<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:29 AM.
 */
use Elasticsearch\Client;
use Environment\Platform;

require __DIR__.'/../../bootstrap.php';

$options = getopt('a', ['gv:']);

$gameVersion = isset($options['gv']) ? $options['gv'] : 'tw';
appendLog('game version: '.$gameVersion);

$base = __DIR__.'/../../../farm-server-conf/';
assert(is_dir($base));

$esHost = '52.19.73.190';
$esPort = 9200;

$esClient = new Client(['hosts' => [sprintf('http://%s:%d/', $esHost, $esPort)]]);
//$docUpdater = new DocumentUpdater($esClient, $gameVersion);
$docUpdater = new NonBlockingDocUpdater();

$platform = new Platform($base);

$updater = new UserStatusUpdater($gameVersion);
$query = array_key_exists('a', $options) ? new AllDeAuthorizedUserQuery() : new DeAuthorizedUserQuery();
$resultSet = $updater->run($platform, $query, $docUpdater);
dump($resultSet);

interface DocumentUpdater
{
    public function update(array $snsidList);
}

class BlockingDocumentUpdater implements DocumentUpdater
{
    /** @var Client */
    protected $client;
    /** @var string */
    protected $index = 'farm';
    /** @var string */
    protected $type;

    /**
     * DocumentUpdater constructor.
     *
     * @param Client $client
     * @param string $gameVersionShort
     */
    public function __construct(Client $client, $gameVersionShort)
    {
        $this->client = $client;
        $this->type = 'user:'.$gameVersionShort;
    }

    /**
     * @param array $snsidList
     *
     * @return array
     */
    public function update(array $snsidList)
    {
        $resultSet = array_map(function ($snsid) {
            $ret = $this->updateUserStatus($snsid, 0);

            return $ret;
        }, $snsidList);

        return $resultSet;
    }

    /**
     * @param string $snsid
     * @param int    $status
     *
     * @return array
     */
    protected function updateUserStatus($snsid, $status)
    {
        assert(is_numeric($status));
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $snsid,
            'body' => '{"doc": {"status": "'.$status.'"}}',
        ];

        try {
            $ret = $this->client->update($params);

            return [
                'snsid' => $snsid,
                'version' => $ret['_version'],
            ];
        } catch (\Exception $e) {
            $errMsg = $e->getMessage();
            $decodedArray = json_decode($errMsg, true);
            if (is_array($decodedArray)) {
                return [
                    'snsid' => $snsid,
                    'error' => $decodedArray['status'],
                ];
            }

            return [
                'snsid' => $snsid,
                'error' => $errMsg,
            ];
        }
    }
}

class NonBlockingDocUpdater implements DocumentUpdater
{
    /**
     * @param array $snsidList
     *
     * @return array
     */
    public function update(array $snsidList)
    {
        $taskFactory = new \Worker\Model\TaskFactory();
        $tasks = array_map(function ($snsid) use ($taskFactory) {
            $url = sprintf('http://52.19.73.190:9200/farm/user:tw/%s/_update', $snsid);
            $postData = sprintf('{"doc":{"status":"%d"}}', time());

            return $taskFactory->create($url, [
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => 1,
            ]);
        }, $snsidList);

        PHP_Timer::start();
        $curlWorker = new \Worker\CurlWorker(new \Worker\Queue\HttpTracer());
        $curlWorker->addTasks($tasks);
        $responseList = [];

        $trace = $curlWorker->run($responseList, function () {
            echo microtime(true).' got response'.PHP_EOL;
        });
        $taskWallTime = PHP_Timer::stop();

        array_walk($responseList, function (array $response) {
            dump(
                [
                    'url' => $response['url'],
                    'http_code' => $response['http_code'],
                    'content' => $response['content'],
                ]
            );
        });

        $httpCost = 0;
        array_walk($trace, function (\Worker\Queue\HttpTracer $httpTracer, $url) use (&$httpCost) {
            $httpCost += $httpTracer->getElapsedTime();
            dump(parse_url($url, PHP_URL_PATH).' => '.$httpTracer);
        });

        dump('time cost on http: '.PHP_Timer::secondsToTimeString($httpCost));
        dump('wall time on http: '.PHP_Timer::secondsToTimeString($taskWallTime));
        dump('Run time: '.PHP_Timer::timeSinceStartOfRequest());
    }
}

class DeAuthorizedUserQuery
{
    /**
     * @return string
     */
    public function getSql()
    {
        return sprintf('select snsid from tbl_user_remove_log where log_date=\'%s\'', date('Y-m-d'));
    }
}

class AllDeAuthorizedUserQuery extends DeAuthorizedUserQuery
{
    /**
     * @return string
     */
    public function getSql()
    {
        return sprintf('select snsid from tbl_user_remove_log');
    }
}

class UserStatusUpdater
{
    /**
     * @param string $gameVersion
     */
    public function __construct($gameVersion)
    {
        $this->gameVersion = $gameVersion;
    }

    /**
     * @param Platform              $platform
     * @param DeAuthorizedUserQuery $query
     * @param DocumentUpdater       $docUpdater
     *
     * @return array
     */
    public function run(Platform $platform, DeAuthorizedUserQuery $query, DocumentUpdater $docUpdater)
    {
        $snsidList = $this->findDeAuthorizedUser($platform, $query);

        return $docUpdater->update($snsidList);
    }

    /**
     * @param Platform              $platform
     * @param DeAuthorizedUserQuery $query
     *
     * @return array
     *
     * @throws Exception
     */
    protected function findDeAuthorizedUser(Platform $platform, DeAuthorizedUserQuery $query)
    {
        $resultSet = [];

        $shardList = $platform->getMySQLShards($this->gameVersion);

        foreach ($shardList as $shardConfig) {
            $dbName = $shardConfig['database'];
            appendLog('on database '.$dbName);
            PHP_Timer::start();
            $snsidList = $this->onShard($shardConfig, $query);
            $delta = PHP_Timer::stop();
            appendLog('cost on database '.$dbName.': '.PHP_Timer::secondsToTimeString($delta));
            $resultSet = array_merge($resultSet, $snsidList);
        }

        return $resultSet;
    }

    /**
     * @param array                 $dbItem
     * @param DeAuthorizedUserQuery $query
     *
     * @return array
     *
     * @throws Exception
     */
    private function onShard(array $dbItem, DeAuthorizedUserQuery $query)
    {
        $pdo = null;
        $dsn = sprintf('mysql:dbname=%s;host=%s', $dbItem['database'], $dbItem['host']);
        try {
            $pdo = new PDO($dsn, $dbItem['username'], $dbItem['password']);
        } catch (\PDOException $e) {
            throw new \Exception('connect to db failed: ['.$dsn.']');
        }

        $statement = $pdo->query($query->getSql());
        $resultSet = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $data) {
            return $data['snsid'];
        }, $resultSet);
    }
}
