<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:52 AM
 */

namespace FBGateway;

use BusinessEntity\Notif;

/**
 * Class FBNotifFactory
 * @package FBGateway
 */
class FBNotifFactory
{
    /**
     * @param Notif $notification
     * @return FBNotif
     */
    public function make(Notif $notification)
    {
        $fbNotification           = new FBNotif();
        $fbNotification->snsid    = $notification->snsid;
        $fbNotification->template = $notification->template;
        $fbNotification->trackRef = $notification->trackRef;

        return $fbNotification;
    }

    /**
     * @param FBNotif $fbNotif
     * @return array
     */
    public function toArray(FBNotif $fbNotif)
    {
        return array(
            'snsid'    => $fbNotif->snsid,
            'template' => $fbNotif->template,
            'trackRef' => $fbNotif->trackRef,
        );
    }

    /**
     * @param Notif[] $notifications
     * @return FBNotif[]
     */
    public function makeList(array $notifications)
    {
        return array_map(array($this, 'make'), $notifications);
    }
}
