<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:30 PM
 */

namespace Repository;

use BusinessEntity\NotificationFactory;
use Persistency\InMemNotifPersist;

/**
 * Class NotifRepoBuilder
 * @package Repository
 */
class NotifRepoBuilder
{
    public function getRepo()
    {
        $persistency = new InMemNotifPersist();
        $factory     = new NotificationFactory();

        $repo = new NotificationRepository($persistency, $factory);

        return $repo;
    }
}
