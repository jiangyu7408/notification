<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:46 PM
 */

namespace Repository;

use BusinessEntity\Notif;
use BusinessEntity\NotifFactory;
use Persistency\InMemNotifListPersist;

/**
 * Class NotifListRepo
 * @package Repository
 */
class NotifListRepo
{
    public function __construct(InMemNotifListPersist $persistency, NotifFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    /**
     * @return Notif[]
     */
    public function getPending()
    {
        $rawList = $this->persistency->retrieve();
        return array_map(array($this, 'makeEntity'), $rawList);
    }

    /**
     * @param array $rawData
     * @return Notif
     */
    private function makeEntity(array $rawData)
    {
        return $this->factory->make($rawData);
    }

    /**
     * @param Notif[] $notifications
     */
    public function markFired(array $notifications)
    {
        $rawList = array_map(array($this, 'fromEntity'), $notifications);
        $this->persistency->persist($rawList);
    }

    /**
     * @param Notif $notification
     * @return array
     */
    private function fromEntity(Notif $notification)
    {
        $this->factory->markFired($notification);
        return $this->factory->toArray($notification);
    }
}
