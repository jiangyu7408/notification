<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:46 PM
 */

namespace Repository;

use BusinessEntity\Notification;
use BusinessEntity\NotificationFactory;
use Persistency\InMemNotifListPersist;

/**
 * Class NotifListRepo
 * @package Repository
 */
class NotifListRepo
{
    public function __construct($appid, InMemNotifListPersist $persistency, NotificationFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    /**
     * @return Notification[]
     */
    public function getPending()
    {
        return $this->persistency->retrieve();
    }

    /**
     * @param Notification[] $notifications
     */
    public function markFired(array $notifications)
    {
        foreach ($notifications as $notification) {
            $this->factory->markFired($notification);
        }

        $this->persistency->persist($notifications);
    }
}
