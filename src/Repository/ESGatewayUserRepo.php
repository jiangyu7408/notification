<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:56 PM.
 */

namespace Repository;

use Elasticsearch\Common\Exceptions\Serializer\JsonErrorException;
use ESGateway\Factory;
use ESGateway\User;
use Persistency\IPersistency;

/**
 * Class ESGatewayUserRepo.
 */
class ESGatewayUserRepo
{
    /**
     * @param IPersistency $persistency
     * @param Factory      $factory
     */
    public function __construct(IPersistency $persistency, Factory $factory)
    {
        $this->persistency = $persistency;
        $this->factory = $factory;
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
        try {
            $this->persistency->persist($users);
        } catch (JsonErrorException $e) {
            dump($e->getMessage());
            dump($list);
        }
    }
}
