<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:20 PM
 */

namespace Repository;

use BusinessEntity\Notification;
use BusinessEntity\NotificationFactory;
use Persistency\IPersistency;

/**
 * Class NotificationRepository
 * @package Repository
 */
class NotificationRepository
{
    public function __construct(IPersistency $persistency, NotificationFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    public function register(Notification $notification)
    {
        $this->persistency->persist($this->factory->parse($notification));
    }
}
