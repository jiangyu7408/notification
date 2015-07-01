<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 2:02 PM
 */

namespace Repository;

use BusinessEntity\NotifFactory;
use Persistency\Storage\NotifArchiveStorage;
use Persistency\Storage\RedisNotifListPersist;
use Persistency\Storage\RedisStorageFactory;

/**
 * Class NotifListRepoBuilder
 * @package Repository
 */
class NotifListRepoBuilder
{
    /**
     * @param int $fireTime
     * @return NotifListRepo
     */
    public function buildRepo($fireTime)
    {
        $redisStorage   = (new RedisStorageFactory())->create();
        $archiveStorage = new NotifArchiveStorage();
        $storage        = new RedisNotifListPersist($redisStorage, $archiveStorage);
        $storage->setFireTime($fireTime);
//        $archiveLocation = $archiveStorage->getLocation($fireTime);
//        var_dump('archive location: ' . $archiveLocation);

        $factory = new NotifFactory();
        $repo    = new NotifListRepo($storage, $factory);
        return $repo;
    }
}
