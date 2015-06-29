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
     * @param Notif $notif
     * @return FBNotif
     */
    public function make(Notif $notif)
    {
        $fbNotif = new FBNotif();

        $keys = array_keys(get_object_vars($fbNotif));

        array_map(function ($key) use ($fbNotif, $notif) {
            $fbNotif->$key = $notif->$key;
        }, $keys);

        return $fbNotif;
    }

    /**
     * @param FBNotif $fbNotif
     * @return array
     */
    public function toArray(FBNotif $fbNotif)
    {
        return get_object_vars($fbNotif);
    }

    /**
     * @param Notif[] $notifications
     * @return FBNotif[]
     */
    public function makeList(array $notifications)
    {
        return array_map([$this, 'make'], $notifications);
    }
}
