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
use Persistency\Storage\RedisStorage;

/**
 * Class NotifListRepoBuilder
 * @package Repository
 */
class NotifListRepoBuilder
{
    /**
     * @param RedisStorage $redisStorage
     * @param NotifArchiveStorage $archiveStorage
     * @return NotifListRepo
     */
    public function buildRepo(RedisStorage $redisStorage, NotifArchiveStorage $archiveStorage)
    {
        $repo = new NotifListRepo(
            new RedisNotifListPersist($redisStorage, $archiveStorage),
            new NotifFactory()
        );
        return $repo;
    }
}
