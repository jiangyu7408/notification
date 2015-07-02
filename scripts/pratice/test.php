<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:29 PM
 */

require __DIR__ . '/../../bootstrap.php';

$dumper = new Ladybug\Dumper();

$redisConfig = \Application\Facade::getInstance()->getRedisConfig();

$dumper->dump($redisConfig);
//$dumper->dump($appContainer->get(RedisQueueConfig::class));
