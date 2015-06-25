<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 2:02 PM
 */

namespace Repository;

use BusinessEntity\NotificationFactory;
use Persistency\InMemNotifListPersist;

/**
 * Class NotifListRepoBuilder
 * @package Repository
 */
class NotifListRepoBuilder
{
    /**
     * @param string $appid
     * @return NotifListRepo
     */
    public function buildRepo($appid)
    {
        $persistency = new InMemNotifListPersist();
        $factory     = new NotificationFactory($appid);
        $repo        = new NotifListRepo($appid, $persistency, $factory);
        return $repo;
    }
}
