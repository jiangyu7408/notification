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
    public $appid;
    public $snsid;
    public $feature;
    public $trackRef;
    public $fireTime;
    public $fired = false;
}
