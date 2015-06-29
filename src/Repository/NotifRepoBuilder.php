<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:30 PM
 */

namespace Repository;

use BusinessEntity\NotifFactory;
use Persistency\Storage\RedisNotifPersist;
use Persistency\Storage\RedisStorageFactory;

/**
 * Class NotifRepoBuilder
 * @package Repository
 */
class NotifRepoBuilder
{
    public function getRepo()
    {
//        $storage = new InMemNotifPersist();
        $storage = new RedisNotifPersist(
            (new RedisStorageFactory())->create()
        );
        $factory = new NotifFactory();

        $repo = new NotifRepo($storage, $factory);

        return $repo;
    }
}
