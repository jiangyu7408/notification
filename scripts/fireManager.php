<?php
/**
 * There is a 'fire queue' as a buffer to Facebook Open Graph Gateway.
 * This script transfer all fire ready notifications into the queue.
 * And then those notifications in the fire queue got fired to Facebook by a fireWorker.
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:45 PM
 */
use Application\Facade;
use FBGateway\FBNotifFactory;
use Persistency\Facebook\GatewayPersistBuilder;
use Repository\FBGatewayRepo;

require __DIR__ . '/../bootstrap.php';

$options = getopt('v', ['fireTime:', 'no-debug']);
$verbose = isset($options['v']);
$fireTime     = isset($options['fireTime']) ? (int)$options['fireTime'] : time();
$debugEnabled = !isset($options['no-debug']);

$timer = (new \Timer\Generator())->shootThenGo($fireTime, time() + 3600);

$fbGatewayConfig = Facade::getInstance()->getFBGatewayOptions();

$gunner         = (new GatewayPersistBuilder())->build($fbGatewayConfig);
$fbNotifFactory = new FBNotifFactory();
$fireMachine    = new FBGatewayRepo($gunner, $fbNotifFactory);

$notifListRepo = Facade::getInstance()->getNotifListRepo();

foreach ($timer as $timestamp) {
    dump(date('Ymd H:i:s', $timestamp));

    $pendingList = $notifListRepo->getPending($timestamp);

    if (count($pendingList) === 0) {
        continue;
    }

    if ($verbose) {
        echo time() . ' pending notifications = ' . count($pendingList) . PHP_EOL;
    }

    $fbNotifList = $fbNotifFactory->makeList($pendingList);
    $fireMachine->burst($fbNotifList);

    $notifListRepo->markFired($pendingList);
}
