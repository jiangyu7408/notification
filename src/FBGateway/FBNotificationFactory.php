<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:52 AM
 */

namespace FBGateway;

use BusinessEntity\Notification;

/**
 * Class FBNotificationFactory
 * @package FBGateway
 */
class FBNotificationFactory
{
    /**
     * @param Notification $notification
     * @return FBNotification
     */
    public function make(Notification $notification)
    {
        $fbNotification           = new FBNotification();
        $fbNotification->snsid    = $notification->snsid;
        $fbNotification->template = $notification->template;
        $fbNotification->trackRef = $notification->trackRef;

        return $fbNotification;
    }
}
