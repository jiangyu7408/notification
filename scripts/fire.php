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

$options      = getopt('', ['fireTime:', 'no-debug']);
$fireTime     = isset($options['fireTime']) ? (int)$options['fireTime'] : time();
$debugEnabled = !isset($options['no-debug']);

$config = require __DIR__ . '/../tests/_fixture/fb.php';

$fbGatewayConfig = $config['good'];
$fbGatewayConfig['queueLocation'] = getQueueLocation();

$gunner         = (new GatewayPersistBuilder())->build($fbGatewayConfig);
$fbNotifFactory = new FBNotifFactory();
$fireMachine    = new FBGatewayRepo($gunner, $fbNotifFactory);

$noPendingEventHandler = function () use ($fireTime, $debugEnabled) {
    if ($debugEnabled) {
        echo 'no pending notifications at fire time: ' . $fireTime . PHP_EOL;
    }
    sleep(1);
};

$pendingNotifList = pendingNotifLists($fireTime, $noPendingEventHandler);

/** @var array $pendingList */
foreach ($pendingNotifList as $pendingList) {
    echo 'pending notifications = ' . count($pendingList) . PHP_EOL;
    $fbNotifList = $fbNotifFactory->makeList($pendingList);
    $fireMachine->burst($fbNotifList);
}

function pendingNotifLists($fireTime, callable $noPendingEventHandler)
{
    while (true) {
        $notifListRepo = (new NotifListRepoBuilder())->buildRepo($fireTime);

        $pendingNotifications = $notifListRepo->getPending();
        if (count($pendingNotifications) === 0) {
            $noPendingEventHandler();
            continue;
        }

        /*
         * yield cpu to outside, and once got running again,
         * the pending notifications are considered as fired successfully
         */
        yield $pendingNotifications;
        $notifListRepo->markFired($pendingNotifications);
    }
}
