<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:45 PM
 */
use BusinessEntity\NotificationFactory;
use Persistency\FireGun;
use Persistency\InMemNotifListPersist;
use Repository\FireMachine;
use Repository\NotifListRepo;

require __DIR__ . '/../bootstrap.php';

$appid = 111;

$ammoDump    = new InMemNotifListPersist();
$ammoFactory = new NotificationFactory($appid);
$ammoLoader  = new NotifListRepo($appid, $ammoDump, $ammoFactory);
$fireMachine = new FireMachine(new FireGun(), $ammoFactory);

$pendingNotifications = $ammoLoader->getPending();

foreach ($pendingNotifications as $notification) {
    $fireMachine->fire($notification);
}

$ammoLoader->markFired($pendingNotifications);
