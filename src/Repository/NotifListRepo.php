<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:46 PM.
 */

namespace Repository;

use BusinessEntity\Notif;
use BusinessEntity\NotifFactory;
use Persistency\Storage\RedisNotifListPersist;

/**
 * Class NotifListRepo.
 */
class NotifListRepo
{
    public function __construct(RedisNotifListPersist $storage, NotifFactory $factory)
    {
        $this->storage = $storage;
        $this->factory = $factory;
    }

    /**
     * @param int $fireTime
     *
     * @return \BusinessEntity\Notif[]
     */
    public function getPending($fireTime)
    {
        $this->storage->setFireTime($fireTime);
        $rawList = $this->storage->retrieve();

        return array_map([$this, 'makeEntity'], $rawList);
    }

    /**
     * @param Notif[] $notifications
     */
    public function markFired(array $notifications)
    {
        $rawList = array_map([$this, 'fromEntity'], $notifications);
        $this->storage->persist($rawList);
    }

    /**
     * @param array $rawData
     *
     * @return Notif
     */
    private function makeEntity(array $rawData)
    {
        return $this->factory->make($rawData);
    }

    /**
     * @param Notif $notification
     *
     * @return array
     */
    private function fromEntity(Notif $notification)
    {
        $this->factory->markFired($notification);

        return $this->factory->toArray($notification);
    }
}
