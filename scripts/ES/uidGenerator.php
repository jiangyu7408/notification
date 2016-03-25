<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 11:29 AM.
 */

/**
 * Example: GV=tw MODE=background php scripts/ES/uidGenerator.php.
 */
require __DIR__.'/../../bootstrap.php';

/**
 * @param string $gameVersion
 *
 * @return Generator
 */
function mysqlDsnGenerator($gameVersion)
{
    $base = __DIR__.'/../../../farm-server-conf/';
    assert(is_dir($base));
    $platform = new \Environment\Platform($base);

    $shardList = $platform->getMySQLShards($gameVersion);
    foreach ($shardList as $shard) {
        yield $shard;
    }
}

/**
 * @param array $options
 *
 * @return string
 */
function makeMySQLDsn(array $options)
{
    return 'mysql:dbname='.$options['database'].';host='.$options['host'];
}

/**
 * @param array $options
 *
 * @return false|PDO
 */
function getMySQLConnection(array $options)
{
    static $connections = [];

    $dsn = makeMySQLDsn($options);
    if (isset($connections[$dsn])) {
        return $connections[$dsn];
    }

    try {
        appendLog('Connect MySQL on DSN: '.$dsn);
        $pdo = new PDO($dsn, $options['username'], $options['password']);
        $connections[$dsn] = $pdo;

        return $pdo;
    } catch (PDOException $e) {
        appendLog('Error: '.$e->getMessage());

        return false;
    }
}

/**
 * @param PDO $pdo
 * @param int $lastActiveTimestamp
 *
 * @return array
 */
function fetchActiveUidList(PDO $pdo, $lastActiveTimestamp)
{
    $query = 'select uid from tbl_user_session force index (time_last_active) where time_last_active>?';
    $statement = $pdo->prepare($query);
    $statement->execute([$lastActiveTimestamp]);

    $uidList = $statement->fetchAll(PDO::FETCH_COLUMN);

    return $uidList;
}

/**
 * @param PDO   $pdo
 * @param array $uidList
 * @param int   $concurrentLevel
 *
 * @return array
 */
function readUserInfo(PDO $pdo, array $uidList, $concurrentLevel = 10)
{
    $result = [];

    $offset = 0;
    while (($concurrent = array_splice($uidList, $offset, $concurrentLevel))) {
        $uids = implode(',', $concurrent);

        $statement = $pdo->prepare('SELECT * from tbl_user where uid in ('.$uids.')');
        $success = $statement->execute();
        if (!$success) {
            dump('PDO Statement Error: '.json_encode($statement->errorInfo()));
            continue;
        }

        $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $row) {
            $result[$row['uid']] = $row;
        }
    }

    return $result;
}

/**
 * @param array $mysqlOptions
 * @param int   $lastActiveTimestamp
 *
 * @return array
 */
function onShard(array $mysqlOptions, $lastActiveTimestamp)
{
    $pdo = getMySQLConnection($mysqlOptions);
    if ($pdo === false) {
        return [];
    }

    PHP_Timer::start();
    $uidList = fetchActiveUidList($pdo, $lastActiveTimestamp);
    $timeCost = PHP_Timer::secondsToTimeString(PHP_Timer::stop());

    appendLog('fetchActiveUidList cost '.$timeCost.' to get result set of size = '.count($uidList));
    if (count($uidList) === 0) {
        return [];
    }

    PHP_Timer::start();
    $details = readUserInfo($pdo, $uidList, 50);
    $timeCost = PHP_Timer::secondsToTimeString(PHP_Timer::stop());
    appendLog('readUserInfo cost '.$timeCost);

    return $details;
}

/**
 * @param string $host
 * @param string $gameVersion
 *
 * @return \Repository\ESGatewayUserRepo
 */
function getESRepo($host, $gameVersion)
{
    $builder = new \Application\ESGatewayBuilder();

    return $builder->buildUserRepo(
        [
            'host' => $host,
            'port' => 9200,
            'index' => 'farm',
            'type' => 'user:'.$gameVersion,
        ]
    );
}

/**
 * @param string $esHost
 * @param string $gameVersion
 * @param array  $users
 */
function batchUpdateES($esHost, $gameVersion, array $users)
{
    if (count($users) === 0) {
        return;
    }

    $repo = getESRepo($esHost, $gameVersion);
    assert($repo instanceof \Repository\ESGatewayUserRepo);
    $factory = $repo->getFactory();
    assert($factory instanceof ESGateway\Factory);

    $esUserList = [];
    foreach ($users as $user) {
        $esUserList[] = $factory->makeUser($user);
    }

    $batchSize = 200;
    $offset = 0;
    while (($batch = array_splice($esUserList, $offset, $batchSize))) {
        $repo->burst($batch);
    }
}

/**
 * @param string $esHost
 * @param string $gameVersion
 * @param int    $lastActiveTimestamp
 */
function main($esHost, $gameVersion, $lastActiveTimestamp)
{
    $dsnList = mysqlDsnGenerator($gameVersion);

    $totalUserCount = 0;
    foreach ($dsnList as $mysqlOptions) {
        $userList = onShard($mysqlOptions, $lastActiveTimestamp);
        if (count($userList) === 0) {
            continue;
        }
        $totalUserCount += count($userList);

        PHP_Timer::start();
        batchUpdateES($esHost, $gameVersion, $userList);
        appendLog('ES update['.count($userList).'] cost: '.PHP_Timer::secondsToTimeString(PHP_Timer::stop()));
    }

    appendLog(
        sprintf(
            '%s total user count [%d], from %s',
            PHP_Timer::resourceUsage(),
            $totalUserCount,
            date('Y/m/d H:i:s', $lastActiveTimestamp)
        )
    );
}

$options = getopt('v', ['gv:', 'es:', 'bs:', 'interval:', 'size:']);

$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$esHost = isset($options['es']) ? $options['es'] : '52.19.73.190';
$backStep = isset($options['bs']) ? $options['bs'] : 1;
$interval = isset($options['interval']) ? $options['interval'] : 20;
$size = isset($options['size']) ? $options['size'] : 100;

$lastActiveTimestamp = time() - $backStep;
$quitTimestamp = time() + $size * $interval;

if ($verbose) {
    $msg = sprintf(
        'game version: %s, ES host: %s, backStep=%d, interval=%d, size=%d, start at: %s, quit at: %s',
        $gameVersion,
        $esHost,
        $backStep,
        $interval,
        $size,
        date('H:i:s', $lastActiveTimestamp),
        date('H:i:s', $quitTimestamp)
    );
    dump($msg);
}

$timer = (new Timer\Generator())->shootThenGo($lastActiveTimestamp, $quitTimestamp);

$step = null;
foreach ($timer as $timestamp) {
    if ($step === null) {
        $step = 0;
        appendLog(date('H:i:s', $lastActiveTimestamp).' run with '.$lastActiveTimestamp);
        main($esHost, $gameVersion, $lastActiveTimestamp);
        continue;
    }

    ++$step;

    if ($verbose) {
        dump(date('His', $timestamp).' step = '.$step);
    }

    if ($step >= $interval) {
        $step = 0;
        $lastRun = $timestamp - $interval - 1;
        appendLog(date('H:i:s').' run with '.$lastRun);
        main($esHost, $gameVersion, $lastRun);
    }
}
