<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:29 AM
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
$docUpdater = new DocumentUpdater($esClient, $gameVersion);

$platform = new Platform($base);

$updater = new UserStatusUpdater($gameVersion);
$query = array_key_exists('a', $options) ? new AllDeAuthorizedUserQuery() : new DeAuthorizedUserQuery();
$resultSet = $updater->run($platform, $query, $docUpdater);
dump($resultSet);

class DocumentUpdater
{
    /**
     * DocumentUpdater constructor.
     *
     * @param Client $client
     * @param string $gameVersionShort
     */
    public function __construct(Client $client, $gameVersionShort)
    {
        $this->client = $client;
        $this->index = 'farm';
        $this->type = 'user:'.$gameVersionShort;
    }

    /**
     * @param string $snsid
     * @param int    $status
     *
     * @return array
     */
    public function updateUserStatus($snsid, $status)
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
     * @param DocumentUpdater       $updater
     *
     * @return array
     */
    public function run(Platform $platform, DeAuthorizedUserQuery $query, DocumentUpdater $updater)
    {
        $snsidList = $this->findDeAuthorizedUser($platform, $query);

        $resultSet = array_map(function ($snsid) use ($updater) {
            $ret = $updater->updateUserStatus($snsid, 0);

            return $ret;
        }, $snsidList);

        return $resultSet;
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

        $statement = $pdo->query($query->getSql());
        $resultSet = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $data) {
            return $data['snsid'];
        }, $resultSet);
    }
}
