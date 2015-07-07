<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 11:29 AM
 */
require __DIR__ . '/../../bootstrap.php';

function mysqlDsnGenerator($gameVersion)
{
    $base = __DIR__ . '/../../../farm-server-conf/';
    assert(is_dir($base));
    $platform = new \Environment\Platform($base);

    $shardList = $platform->getMySQLShards($gameVersion);
    foreach ($shardList as $shard) {
        yield $shard;
    }
}

function memcacheDsnGenerator()
{
    return [
        'mc_farm' => [
            'driver'      => 'memcached',
            'servers'     => [
                ['memcached_1', 11203]
            ],
            'compression' => false,
            'lifetime'    => 3600,     // 默认1小时
            'persistent'  => true
        ]
    ];
}

function getMemcacheConnection(array $options)
{
    static $connection = null;

    if ($connection === null) {
        $connection = new Memcached();
        $success    = $connection->addServers($options['servers']);
        assert($success);
    }

    return $connection;
}

function makeMySQLDsn(array $options)
{
    return 'mysql:dbname=' . $options['database'] . ';host=' . $options['host'];
}

function getMySQLConnection(array $options)
{
    static $connections = [];

    $dsn = makeMySQLDsn($options);
    if (isset($connections[$dsn])) {
        return $connections[$dsn];
    }

    $pdo = null;
    try {
        dump('Connect MySQL on DSN: ' . $dsn);
        $pdo = new PDO($dsn, $options['username'], $options['password']);
    } catch (PDOException $e) {
        dump('Error: ' . $e->getMessage());
        return false;
    }

    $connections[$dsn] = $pdo;

    return $pdo;
}

function fetchActiveUidList(PDO $pdo, $lastActiveTimestamp)
{
    $query     = 'select uid from tbl_user_session force index (time_last_active) where time_last_active>?';
    $statement = $pdo->prepare($query);
    $statement->execute([$lastActiveTimestamp]);

    $uidList = $statement->fetchAll(PDO::FETCH_COLUMN);
    return $uidList;
}

function getUserInfo($uidList)
{
    $memcacheOptions = memcacheDsnGenerator();
    $memcacheClient  = getMemcacheConnection($memcacheOptions['mc_farm']);

    dump($uidList);
    dump(array_values($uidList));
    $list = $memcacheClient->getMulti($uidList);
    dump($list);
}

function readUserInfo(PDO $pdo, array $uidList)
{
    $result = [];

    $concurrentLevel = 10;

    $offset = 0;
    while (($concurrent = array_splice($uidList, $offset, $concurrentLevel))) {
        $uids = implode(',', $concurrent);

        $statement = $pdo->prepare('SELECT * from tbl_user where uid in (' . $uids . ')');
        $success   = $statement->execute();
        if (!$success) {
            dump('PDO Statement Error: ' . print_r($statement->errorInfo(), true));
            continue;
        }

        dump($statement->queryString);

        $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $row) {
            $result[$row['uid']] = $row;
        }
    }

    return $result;
}

function onShard(array $mysqlOptions, $lastActiveTimestamp)
{
    $pdo = getMySQLConnection($mysqlOptions);
    if ($pdo === false) {
        return [];
    }

    PHP_Timer::start();
    $uidList  = fetchActiveUidList($pdo, $lastActiveTimestamp);
    $timeCost = PHP_Timer::secondsToTimeString(PHP_Timer::stop());
    dump('fetchActiveUidList cost ' . $timeCost);
    foreach ($uidList as $uid) {
        assert(is_numeric($uid));
    }

    PHP_Timer::start();
//    getUserInfo($uidList);
    $details  = readUserInfo($pdo, $uidList);
    $timeCost = PHP_Timer::secondsToTimeString(PHP_Timer::stop());
    dump('readUserInfo cost ' . $timeCost);

    return $details;
}

function getTestRepo($host, $gameVersion)
{
    $builder = new \Application\ESGatewayBuilder();
    return $builder->buildUserRepo([
        'host' => $host,
        'port'  => 9200,
        'index' => 'farm',
        'type' => 'user:' . $gameVersion
    ]);
}

function batchUpdateES($esHost, $gameVersion, array $users)
{
    $repo = getTestRepo($esHost, $gameVersion);
    assert($repo instanceof \Repository\ESGatewayUserRepo);
    $factory = $repo->getFactory();
    assert($factory instanceof ESGateway\Factory);

    $esUserList = [];
    foreach ($users as $user) {
        $esUserList[] = $factory->makeUser($user);
    }

    $batchSize = 10;
    $offset    = 0;
    while (($batch = array_splice($esUserList, $offset, $batchSize))) {
        $repo->burst($batch);
    }
}

function main($esHost, $gameVersion)
{
    $lastActiveTimestamp = time() - 3600;
    $dsnList = mysqlDsnGenerator($gameVersion);
    foreach ($dsnList as $mysqlOptions) {
        $userList = onShard($mysqlOptions, $lastActiveTimestamp);

        PHP_Timer::start();
        batchUpdateES($esHost, $gameVersion, $userList);
        dump('ES update[' . count($userList) . '] cost: ' . PHP_Timer::secondsToTimeString(PHP_Timer::stop()));
    }

    dump(PHP_Timer::resourceUsage());
}

$options = getopt('v', ['gv:', 'es:']);

$verbose = isset($options['v']);

$gameVersion = isset($options['gv']) ? $options['gv'] : 'tw';
$esHost = isset($options['es']) ? $options['es'] : '54.72.159.81';
if ($verbose) {
    dump('game version: ' . $gameVersion . ', ES host: ' . $esHost);
}

main($esHost, $gameVersion);

