<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:16 PM
 */
use Repository\NotifRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$options = getopt('', [
    'fireTime:'
]);

$fireTime = isset($options['fireTime']) ? (int)$options['fireTime'] : time();

$repo = (new NotifRepoBuilder())->getRepo();

$feature  = 'feature' . mt_rand(1, 2);
$trackRef = $feature . '_' . mt_rand(1, 10);
$template = 'test only';

$config = require __DIR__ . '/../tests/_fixture/fb.php';
$snsid  = $config['bad']['snsid'];
$appid  = $config['bad']['appId'];

$notification           = new \BusinessEntity\Notif();
$notification->snsid    = $snsid;
$notification->appid    = $appid;
$notification->feature  = $feature;
$notification->fired    = false;
$notification->fireTime = $fireTime;
$notification->template = $template;
$notification->trackRef = $trackRef;

$repo->register($notification);
