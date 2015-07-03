<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 2:41 PM
 */

$redisOptions = require 'redis.php';
return array_merge($redisOptions, [
    'prefix' => 'notif'
]);
