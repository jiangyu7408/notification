<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 2:02 PM
 */

namespace Repository;

use BusinessEntity\NotifFactory;
use Persistency\Storage\InMemNotifListPersist;

/**
 * Class NotifListRepoBuilder
 * @package Repository
 */
class NotifListRepoBuilder
{
    /**
     * @return NotifListRepo
     */
    public function buildRepo()
    {
        $storage = new InMemNotifListPersist();
        $factory = new NotifFactory();
        $repo    = new NotifListRepo($storage, $factory);
        return $repo;
    }
}
