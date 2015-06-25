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

$appid = 111;

$notifListRepo = (new NotifListRepoBuilder())->buildRepo();

$pendingNotifications = $notifListRepo->getPending();
if (count($pendingNotifications) === 0) {
    return;
}

$fbNotifFactory = new FBNotifFactory();
$fbNotifList    = $fbNotifFactory->makeList($pendingNotifications);

$gunner      = (new FBGatewayPersistBuilder())->build($appid);
$fireMachine = new FBGatewayRepo($gunner, $fbNotifFactory);
$fireMachine->burst($fbNotifList);

$notifListRepo->markFired($pendingNotifications);
