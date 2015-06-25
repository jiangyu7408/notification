<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:53 PM
 */

namespace BusinessEntity;

/**
 * Class NotificationFactory
 * @package BusinessEntity
 */
class NotificationFactory
{
    /**
     * @param array $input
     * @return Notification
     */
    public function make(array $input)
    {
        $notification           = new Notification();
        $notification->appid    = $input['appid'];
        $notification->snsid    = $input['snsid'];
        $notification->fireTime = $input['fireTime'];
        $notification->feature  = $input['feature'];
        $notification->trackRef = $input['trackRef'];

        return $notification;
    }

    /**
     * @param Notification $notification
     * @return array
     */
    public function parse(Notification $notification)
    {
        return array(
            'appid'    => $notification->appid,
            'snsid'    => $notification->snsid,
            'fireTime' => $notification->fireTime,
            'feature'  => $notification->feature,
            'trackRef' => $notification->trackRef,
        );
    }

    /**
     * @param Notification $notification
     * @return Notification
     */
    public function markFired(Notification $notification)
    {
        $notification->fired = true;
        return $notification;
    }
}
