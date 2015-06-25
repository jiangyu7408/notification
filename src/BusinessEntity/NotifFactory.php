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
        $notif = new Notif();

        $keys = array_keys(get_object_vars($notif));

        array_map(function ($key) use ($notif, $input) {
            $notif->$key = $input[$key];
        }, $keys);

        return $notif;
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
     * @param Notif $notif
     * @return Notif
     */
    public function markFired(Notif $notif)
    {
        $notif->fired = true;
        return $notif;
    }
}
