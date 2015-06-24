<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:57 PM
 */

namespace Repository;

use BusinessEntity\Notification;
use BusinessEntity\NotificationFactory;
use Persistency\IPersistency;

/**
 * Class FireMachine
 * @package Repository
 */
class FireMachine
{
    public function __construct(IPersistency $persistency, NotificationFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    public function fire(Notification $notification)
    {
        $this->persistency->persist($this->loadBullet($notification));
    }

    private function loadBullet(Notification $notification)
    {
        return $this->factory->parse($notification);
    }
}
