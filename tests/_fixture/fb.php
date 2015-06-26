<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 11:36 AM
 */

return array(
    'good' => array(
        'snsid'             => '100001349218797',
        'appId'             => '611186132271595',
        'secretKey'         => '8c43357c9a0425192f9af574d8c9fa06',
        'openGraphEndpoint' => 'https://graph.facebook.com/UID/notifications'
    ),
    'bad'  => array(
        'snsid'             => '100001349218797',
        'appId'             => 'appId',
        'secretKey'         => 'secretKey',
        'openGraphEndpoint' => 'bad://graph.facebook.com/UID/notifications'
    )
);
