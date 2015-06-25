<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:53 PM
 */

namespace BusinessEntity;

/**
 * Class NotifFactory
 * @package BusinessEntity
 */
class NotifFactory
{
    /**
     * @param array $input
     * @return Notif
     */
    public function make(array $input)
    {
        $notification           = new Notif();
        $notification->appid    = $input['appid'];
        $notification->snsid    = $input['snsid'];
        $notification->fireTime = $input['fireTime'];
        $notification->feature  = $input['feature'];
        $notification->trackRef = $input['trackRef'];

        return $notification;
    }

    /**
     * @param Notif $notif
     * @return array
     */
    public function toArray(Notif $notif)
    {
        return get_object_vars($notif);
    }

    /**
     * @param Notif $notification
     * @return Notif
     */
    public function markFired(Notif $notification)
    {
        $notification->fired = true;
        return $notification;
    }
}
