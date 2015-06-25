<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:20 PM
 */

namespace Repository;

use BusinessEntity\Notif;
use BusinessEntity\NotifFactory;
use Persistency\Storage\AbstractStorage;

/**
 * Class NotifRepo
 * @package Repository
 */
class NotifRepo
{
    public function __construct(AbstractStorage $storage, NotifFactory $factory)
    {
        $this->storage = $storage;
        $this->factory = $factory;
    }

    public function register(Notif $notification)
    {
        $this->storage->persist($this->factory->toArray($notification));
    }
}
