<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:45 PM
 */
use FBGateway\FBNotifFactory;
use Persistency\FBGatewayPersistBuilder;
use Repository\FBGatewayRepo;
use Repository\NotifListRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$options = getopt('', [
    'fireTime:'
]);

$fireTime = isset($options['fireTime']) ? (int)$options['fireTime'] : time();

$appid = 111;

$notifListRepo = (new NotifListRepoBuilder())->buildRepo($fireTime);

$pendingNotifications = $notifListRepo->getPending();
if (count($pendingNotifications) === 0) {
    echo 'no pending notifications' . PHP_EOL;
    return;
}

echo 'pending notifications = ' . count($pendingNotifications) . PHP_EOL;

$fbNotifFactory = new FBNotifFactory();
$fbNotifList    = $fbNotifFactory->makeList($pendingNotifications);

$config = require __DIR__ . '/../tests/_fixture/fb.php';

$gunner = (new FBGatewayPersistBuilder())->build($config['bad']);
$fireMachine = new FBGatewayRepo($gunner, $fbNotifFactory);
$fireMachine->burst($fbNotifList);

$notifListRepo->markFired($pendingNotifications);
