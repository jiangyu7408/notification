<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:57 PM
 */

namespace Repository;

use FBGateway\FBNotif;
use FBGateway\FBNotifFactory;
use Persistency\IPersistency;

/**
 * Class FBGatewayRepo
 * @package Repository
 */
class FBGatewayRepo
{
    /**
     * @param IPersistency $persistency
     * @param FBNotifFactory $factory
     */
    public function __construct(IPersistency $persistency, FBNotifFactory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    /**
     * @param FBNotif $notification
     */
    public function fire(FBNotif $notification)
    {
        $this->persistency->persist($this->factory->toArray($notification));
    }

    /**
     * @param FBNotif[] $list
     */
    public function burst(array $list)
    {
        array_map(array($this, 'fire'), $list);
    }
}
