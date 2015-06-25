<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:45 PM
 */
use BusinessEntity\Notification;
use FBGateway\FBNotificationFactory;
use Persistency\FireGunBuilder;
use Repository\FireMachine;
use Repository\NotifListRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$appid = 111;

$notifListRepo = (new NotifListRepoBuilder())->buildRepo($appid);

$pendingNotifications = $notifListRepo->getPending();
if (count($pendingNotifications) === 0) {
    return;
}

$gunner      = (new FireGunBuilder())->buildFireGun($appid);
$fireMachine = new FireMachine($gunner);

$fbNotifFactory = new FBNotificationFactory();

array_map(function (Notification $notification) use ($fbNotifFactory, $fireMachine) {
    $fireMachine->fire($fbNotifFactory->make($notification));
}, $pendingNotifications);

$notifListRepo->markFired($pendingNotifications);
