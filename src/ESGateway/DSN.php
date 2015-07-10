<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:30 PM.
 */

namespace ESGateway;

/**
 * Class DSN.
 */
class DSN
{
    public $ip;
    public $port;

    public function toString()
    {
        return 'http://'.$this->ip.':'.$this->port;
    }
}
