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
use FBGateway\FBNotifFactory;
use Persistency\Facebook\GatewayPersistBuilder;
use Repository\FBGatewayRepo;
use Repository\NotifListRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$options = getopt('', ['fireTime:']);
$fireTime = isset($options['fireTime']) ? (int)$options['fireTime'] : time();

$notifListRepo = (new NotifListRepoBuilder())->buildRepo($fireTime);

$pendingNotifications = $notifListRepo->getPending();
if (count($pendingNotifications) === 0) {
    echo 'no pending notifications at fire time: ' . $fireTime . PHP_EOL;
    return;
}

echo 'pending notifications = ' . count($pendingNotifications) . PHP_EOL;

$config = require __DIR__ . '/../tests/_fixture/fb.php';

$fbGatewayConfig                  = $config['bad'];
$fbGatewayConfig['queueLocation'] = getQueueLocation();

$gunner         = (new GatewayPersistBuilder())->build($fbGatewayConfig);
$fbNotifFactory = new FBNotifFactory();
$fireMachine    = new FBGatewayRepo($gunner, $fbNotifFactory);

$fbNotifList = $fbNotifFactory->makeList($pendingNotifications);
$fireMachine->burst($fbNotifList);

$notifListRepo->markFired($pendingNotifications);
