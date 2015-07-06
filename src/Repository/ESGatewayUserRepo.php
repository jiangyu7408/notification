<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:56 PM
 */

namespace Repository;

use ESGateway\Factory;
use ESGateway\User;
use Persistency\IPersistency;

/**
 * Class ESGatewayUserRepo
 * @package Repository
 */
class ESGatewayUserRepo
{
    /**
     * @param IPersistency $persistency
     * @param Factory $factory
     */
    public function __construct(IPersistency $persistency, Factory $factory)
    {
        $this->persistency = $persistency;
        $this->factory     = $factory;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param User $user
     */
    public function fire(User $user)
    {
        $this->persistency->persist([$this->factory->toArray($user)]);
    }

    /**
     * @param User[] $list
     */
    public function burst(array $list)
    {
        $users = [];
        foreach ($list as $user) {
            $users[] = $this->factory->toArray($user);
        }
        $this->persistency->persist($users);
    }
}
