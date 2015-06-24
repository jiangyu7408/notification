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
 * Class NotifRepoFactory
 * @package Repository
 */
class NotifRepoFactory
{
    public function getRepo($appid)
    {
        $persistency = new InMemNotifPersist();
        $factory     = new NotificationFactory($appid);

        $repo = new NotificationRepository($appid, $persistency, $factory);

        return $repo;
    }
}
