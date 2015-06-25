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
use Persistency\IPersistency;

/**
 * Class NotifRepo
 * @package Repository
 */
class NotifRepo
{
    public function __construct(IPersistency $persistency, NotifFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    public function register(Notif $notification)
    {
        $this->persistency->persist($this->factory->toArray($notification));
    }
}
