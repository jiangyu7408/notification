<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/17
 * Time: 2:13 PM
 */

require __DIR__ . '/vendor/autoload.php';

function getQueueLocation()
{
    $header = require 'scripts/fireHeader.php';

    if (!isset($header['queueLocation'])) {
        trigger_error('check fireHeader.php of queueLocation');
    }
    return trim($header['queueLocation']);
}
