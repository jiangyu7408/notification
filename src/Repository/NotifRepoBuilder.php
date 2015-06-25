<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 6:30 PM
 */

namespace Repository;

use BusinessEntity\NotifFactory;
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
        $factory     = new NotifFactory();

        $repo = new NotifRepo($persistency, $factory);

        return $repo;
    }
}
