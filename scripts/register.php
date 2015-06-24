<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:16 PM
 */
use BusinessEntity\NotificationFactory;
use Repository\NotifRepoFactory;

require __DIR__ . '/../bootstrap.php';

$options = getopt('', array(
    'app:',
    'snsid:'
));

$appid = trim($options['app']);

$repo = (new NotifRepoFactory())->getRepo($appid);

$notification = (new NotificationFactory($appid))->make(array(
    'snsid'    => $options['snsid'],
    'feature'  => 'feature' . mt_rand(1, 2),
    'trackRef' => time(),
));

$repo->register($notification);
