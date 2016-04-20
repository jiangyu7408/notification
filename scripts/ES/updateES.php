<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/18
 * Time: 18:33.
 */

use Database\PdoFactory;
use DataProvider\User\UserDetailGenerator;

require __DIR__.'/../../bootstrap.php';

$options = getopt(
    'v',
    [
        'gv:',
        'es:',
        'uid:',
    ]
);
$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$esHost = isset($options['es']) ? $options['es'] : '52.19.73.190';
assert(isset($options['uid']), 'uid not defined');
$uid = trim($options['uid']);

$msg = sprintf(
    'game version: %s, ES host: %s, uid: %s',
    $gameVersion,
    $esHost,
    $uid
);
$verbose && dump($msg);

$users = null;
$groupedUsers = UserDetailGenerator::find($gameVersion, [$uid]);
foreach ($groupedUsers as $eachUserList) {
    if (count($eachUserList) === 0) {
        continue;
    }
    $users = $eachUserList;
    break;
}
$verbose && dump($users);

$uidList = [];
foreach ($users as $uid => $user) {
    $uidList[$user['snsid']] = $uid;
}

$configGenerator = \Database\ShardHelper::shardConfigGenerator($gameVersion);
foreach ($configGenerator as $shardConfig) {
    $shardId = $shardConfig['shardId'];
    dump(sprintf('on shard %s', $shardId));
    $pdo = PdoFactory::makePool($gameVersion)->getByShardId($shardId);

    $common = \DataProvider\User\CommonInfoProvider::readUserInfo($pdo, $uidList);
    if ($common) {
        dump($common);
        break;
    }
}

$pdo = PdoFactory::makeGlobalPdo($gameVersion);
$paymentDigestList = \DataProvider\User\PaymentInfoProvider::readUserInfo($pdo, $uidList);
if ($paymentDigestList[$uid]) {
    dump($paymentDigestList);
}

foreach ($common as $uid => $user) {
    if (isset($paymentDigestList[$uid])) {
        $common[$uid]['history_pay_amount'] = $paymentDigestList[$uid]->historyPayAmount;
        $common[$uid]['last_pay_time'] = (int) $paymentDigestList[$uid]->lastPayTime;
        $common[$uid]['last_pay_amount'] = $paymentDigestList[$uid]->lastPayAmount;
    }
}
dump($common);

foreach ($common as $user) {
    $data = (new \ESGateway\Factory())->makeUser($user);
    dump($data);
}

$config = [
    'host' => $esHost,
    'port' => 9200,
    'index' => 'farm',
    'type' => 'user:'.$gameVersion,
];
$indexer = new \Facade\ES\Indexer($config, 1);
$delta = $indexer->batchUpdate($users);
dump($indexer->getLastRoundData());
dump('cost '.PHP_Timer::secondsToTimeString($delta[0]));
