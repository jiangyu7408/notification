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
    /** @var string */
    public $host;
    /** @var int */
    public $port;

    /**
     * DSN constructor.
     *
     * @param string $host
     * @param int    $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'http://'.$this->host.':'.$this->port;
    }
}
