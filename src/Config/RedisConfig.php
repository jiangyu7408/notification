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
}
