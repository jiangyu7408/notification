<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/17
 * Time: 2:13 PM
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('assert.active', '1');
ini_set('assert.warning', '1');
ini_set('assert.bail', '1');

require __DIR__ . '/vendor/autoload.php';

define('CONFIG_DIR', __DIR__);

function getQueueLocation()
{
    $header = require 'scripts/fireHeader.php';

    if (!isset($header['queueLocation'])) {
        trigger_error('check fireHeader.php of queueLocation');
    }
    return trim($header['queueLocation']);
}
