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
    /** @var \Worker\Model\TaskFactory */
    protected $taskFactory;
    protected $concurrency;
    protected $taskWallTime = 0;
    protected $httpCost = 0;
    /** @var int */
    protected $total;
    /** @var int */
    protected $processed;

    /**
     * NonBlockingDocUpdater constructor.
     *
     * @param int $concurrency
     */
    public function __construct($concurrency = 100)
    {
        $this->concurrency = (int) $concurrency;
        assert($this->concurrency > 0);
        $this->taskFactory = new \Worker\Model\TaskFactory();
    }

    /**
     * @param array $snsidList
     *
     * @return array
     */
    public function update(array $snsidList)
    {
        $this->total = count($snsidList);
        dump('total = '.$this->total);
        $resultSet = [
            200 => 0,
            404 => 0,
            409 => 0,
        ];
        $cursor = 0;
        while (true) {
            $batch = array_slice($snsidList, $cursor, $this->concurrency);
            $size = count($batch);
            if ($size === 0) {
                break;
            }
            $cursor += $size;
            $ret = $this->onBatch($batch);
            foreach ($ret as $httpCode) {
                $resultSet[$httpCode]++;
            }
        }
        echo PHP_EOL;

        dump('   time cost on http: '.PHP_Timer::secondsToTimeString($this->httpCost));
        dump('average cost on http: '.PHP_Timer::secondsToTimeString($this->httpCost / $this->total));
        dump('   wall time on http: '.PHP_Timer::secondsToTimeString($this->taskWallTime));

        return $resultSet;
    }

    protected function onBatch(array $batch)
    {
        $tasks = $this->taskBatchFactory($batch);

        PHP_Timer::start();
        $curlWorker = new \Worker\CurlWorker(new \Worker\Queue\HttpTracer());
        $curlWorker->addTasks($tasks, $this->concurrency);
        $responseList = [];

        $trace = $curlWorker->run($responseList, function () {
            printf("%10d/%d\r", ++$this->processed, $this->total);
        });
        $this->taskWallTime += PHP_Timer::stop();

        array_walk($trace, function (\Worker\Queue\HttpTracer $httpTracer) {
            $this->httpCost += $httpTracer->getElapsedTime();
        });

        return array_map(function (array $response) {
            return (int) $response['http_code'];
        }, $responseList);
    }

    /**
     * @param array $snsidList
     *
     * @return array
     */
    protected function taskBatchFactory(array $snsidList)
    {
        return array_map(function ($snsid) {
            $url = sprintf('http://52.19.73.190:9200/farm/user:tw/%s/_update', $snsid);
            $postData = sprintf('{"doc":{"status":"%d"}}', 0);

            return $this->taskFactory->create($url, [
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => 1,
            ]);
        }, $snsidList);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function parseSnsid($url)
    {
        $arr = explode('/', parse_url($url, PHP_URL_PATH));

        return (string) $arr[3];
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
    /** @var int */
    protected $fromTs;
    /** @var int */
    protected $toTs;

    /**
     * AllDeAuthorizedUserQuery constructor.
     *
     * @param string $date
     */
    public function __construct($date)
    {
        $dateTime = new DateTime($date);
        $this->fromTs = $dateTime->getTimestamp();
        if (strlen($date) === strlen('2016-03')) {
            $endDateTime = $dateTime->add(new DateInterval('P1M'));
            $this->toTs = $endDateTime->getTimestamp();
        }
    }

    /**
     * @return string
     */
    public function getSql()
    {
        $sql = sprintf('select snsid from tbl_user_remove_log where log_time>='.$this->fromTs);
        if ($this->toTs) {
            $sql .= ' and log_time<='.$this->toTs;
        }

        return $sql;
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
            dump(sprintf('Memory: %4.2fMb', memory_get_peak_usage(true) / 1048576));
            $resultSet = array_merge($resultSet, $snsidList);
        }

        return $resultSet;
    }

    /**
     * @param array                 $dbItem
     * @param DeAuthorizedUserQuery $query
     *
     * @return array
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

        $sql = $query->getSql();
        dump($sql);
        $statement = $pdo->query($sql);
        $resultSet = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $data) {
            return $data['snsid'];
        }, $resultSet);
    }
}

$options = getopt('', ['concurrency:', 'date:', 'gv:']);

$concurrency = isset($options['concurrency']) ? (int) $options['concurrency'] : 100;
$gameVersion = isset($options['gv']) ? $options['gv'] : 'tw';
appendLog('game version: '.$gameVersion.', concurrency: '.$concurrency);

$base = __DIR__.'/../../../farm-server-conf/';
assert(is_dir($base));

$esHost = '52.19.73.190';
$esPort = 9200;

$esClient = new Client(['hosts' => [sprintf('http://%s:%d/', $esHost, $esPort)]]);
//$docUpdater = new DocumentUpdater($esClient, $gameVersion);
$docUpdater = new NonBlockingDocUpdater($concurrency);

$platform = new Platform($base);

$updater = new UserStatusUpdater($gameVersion);
dump($options);
$query = array_key_exists('date', $options)
    ? new AllDeAuthorizedUserQuery($options['date'])
    : new DeAuthorizedUserQuery();
$resultSet = $updater->run($platform, $query, $docUpdater);
dump($resultSet);

dump('Run time: '.PHP_Timer::timeSinceStartOfRequest());
dump(sprintf('Memory: %4.2fMb', memory_get_peak_usage(true) / 1048576));
