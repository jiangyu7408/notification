<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/06
 * Time: 15:21.
 */
use DataProvider\User\ActiveUidProvider;

require __DIR__.'/../../bootstrap.php';

spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

$options = getopt('v', ['gv:']);

$verbose = isset($options['v']);
assert(isset($options['gv']), 'game version not defined');
$gameVersion = trim($options['gv']);

$lastActiveTimestamp = (new DateTime())->setTime(0, 0, 0)->getTimestamp();
$groupedUidList = ActiveUidProvider::generate($gameVersion, $lastActiveTimestamp, $verbose);

$stats = [];
array_walk(
    $groupedUidList,
    function (array $uidList, $shardId) use (&$stats) {
        $count = count($uidList);
        $stats[$shardId] = $count;
    }
);

dump($stats);
dump('Total user count: '.array_sum($stats));
