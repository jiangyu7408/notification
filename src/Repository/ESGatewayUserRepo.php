<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:56 PM.
 */

namespace Repository;

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
     * @param User   $user
     * @param string $errorInfo
     *
     * @return bool
     */
    public function fire(User $user, &$errorInfo)
    {
        return $this->burst([$user], $errorInfo);
    }

    /**
     * @param User[] $list
     * @param string $errorString
     *
     * @return bool
     */
    public function burst(array $list, &$errorString)
    {
        $users = [];
        foreach ($list as $user) {
            $users[] = $this->factory->toArray($user);
        }

        $errorString = null;
        try {
            $this->persistency->persist($users);
        } catch (\RuntimeException $e) {
            $errorString = $e->getMessage();
        }

        if ($errorString) {
            return false;
        }

        return true;
    }
}
