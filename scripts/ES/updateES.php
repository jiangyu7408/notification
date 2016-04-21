<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/18
 * Time: 18:33.
 */

use Database\PdoFactory;
use DataProvider\User\UserDetailProvider;

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

$provider = new UserDetailProvider($gameVersion, PdoFactory::makePool($gameVersion));
$groupedUserList = array_filter($provider->find([$uid]));
if ($verbose) {
    dump(__FILE__);
    dump($groupedUserList);
}

$config = [
    'host' => $esHost,
    'port' => 9200,
    'index' => 'farm',
    'type' => 'user:'.$gameVersion,
];
$indexer = new \Facade\ES\Indexer($config, 1);
foreach ($groupedUserList as $shardId => $shardUserList) {
    $delta = $indexer->batchUpdate($shardUserList);
    $batchResult = $indexer->getBatchResult();
    dump(__FILE__);
    array_map(
        function ($errorString) {
            $decoded = json_decode($errorString, true);
            if (is_array($decoded)) {
                dump($decoded);
            } else {
                dump($errorString);
            }
        },
        $batchResult
    );
//    dump($indexer->getLastRoundData());
    dump('cost '.PHP_Timer::secondsToTimeString($delta[0]));
}
