<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:57 PM
 */

namespace Repository;

use FBGateway\FBNotification;
use Persistency\IPersistency;

/**
 * Class FireMachine
 * @package Repository
 */
class FireMachine
{
    public function __construct(IPersistency $persistency)
    {
        $this->persistency = $persistency;
    }

    public function fire(FBNotification $notification)
    {
        $this->persistency->persist($notification->toArray());
    }
}
