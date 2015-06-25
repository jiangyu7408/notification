<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:44 PM
 */

namespace BusinessEntity;

/**
 * Class Notification
 * @package BusinessEntity
 */
class Notification
{
    /**
     * @var string
     */
    public $appid;
    /**
     * @var string
     */
    public $snsid;
    /**
     * @var string
     */
    public $feature;
    /**
     * @var string
     */
    public $template;
    /**
     * @var string
     */
    public $trackRef;
    /**
     * @var int
     */
    public $fireTime;
    /**
     * @var bool
     */
    public $fired = false;
}
