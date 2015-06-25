<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:16 PM
 */
use BusinessEntity\NotifFactory;
use Repository\NotifRepoBuilder;

require __DIR__ . '/../bootstrap.php';

$options = getopt('', array(
    'app:',
    'snsid:'
));

//$appid = trim($options['app']);
//$snsid = trim($options['snsid']);
$appid = 111;
$snsid = '675097095878591';

$repo = (new NotifRepoBuilder())->getRepo();

$feature  = 'feature' . mt_rand(1, 2);
$trackRef = $feature . '_' . mt_rand(1, 10);
$fireTime = time() + mt_rand(1, 10);

$notification = (new NotifFactory())->make(array(
    'appid'    => $appid,
    'snsid'    => $snsid,
    'feature'  => $feature,
    'fireTime' => $fireTime,
    'trackRef' => $trackRef,
));

$repo->register($notification);
