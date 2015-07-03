<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:30 PM
 */

namespace Repository;

use BusinessEntity\NotifFactory;
use Config\RedisNotifConfig;
use Persistency\Storage\RedisNotifPersist;
use Persistency\Storage\RedisStorageFactory;

/**
 * Class NotifRepoBuilder
 * @package Repository
 */
class NotifRepoBuilder
{
    public function getRepo(RedisNotifConfig $config)
    {
        $storage = new RedisNotifPersist(
            (new RedisStorageFactory())->create($config, $config->prefix)
        );
        $factory = new NotifFactory();

        $repo = new NotifRepo($storage, $factory);

        return $repo;
    }
}
