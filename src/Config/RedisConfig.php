<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 5:44 PM
 */

namespace Config;

/**
 * Class RedisConfig
 * @package ConfigContainer
 */
class RedisConfig
{
    /**
     * @var string
     */
    public $scheme;
    /**
     * @var string
     */
    public $host;
    /**
     * @var int
     */
    public $port;
    /**
     * @var int
     */
    public $timeout;

    /**
     * @return string
     */
    public function toString()
    {
        return $this->scheme . '://' . $this->host . ':' . $this->port;
    }

    /**
     * @return string
     */
    public function hash()
    {
        return md5(json_encode($this->toArray()));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
