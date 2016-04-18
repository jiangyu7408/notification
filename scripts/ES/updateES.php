<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/18
 * Time: 18:33.
 */

require __DIR__.'/../../bootstrap.php';

spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

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
$groupedUsers = \script\UserDetailGenerator::find($gameVersion, [$uid]);
foreach ($groupedUsers as $eachUserList) {
    if (count($eachUserList) === 0) {
        continue;
    }
    $users = $eachUserList;
    break;
}
$verbose && dump($users);

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
