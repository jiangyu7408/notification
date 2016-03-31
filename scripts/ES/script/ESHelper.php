<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:05.
 */
namespace script;

use Application\ESGatewayBuilder;
use ESGateway\Factory;
use Repository\ESGatewayUserRepo;

/**
 * Class ESHelper.
 */
class ESHelper
{
    /**
     * @param string $esHost
     * @param string $gameVersion
     * @param array  $users
     */
    public static function batchUpdate($esHost, $gameVersion, array $users)
    {
        if (count($users) === 0) {
            return;
        }

        $repo = self::getESRepo($esHost, $gameVersion);
        assert($repo instanceof ESGatewayUserRepo);
        $factory = $repo->getFactory();
        assert($factory instanceof Factory);

        $esUserList = [];
        foreach ($users as $user) {
            $esUserList[] = $factory->makeUser($user);
        }

        $batchSize = 200;
        $offset = 0;
        while (($batch = array_splice($esUserList, $offset, $batchSize))) {
            $repo->burst($batch);
        }
    }

    /**
     * @param string $host
     * @param string $gameVersion
     *
     * @return ESGatewayUserRepo
     */
    protected static function getESRepo($host, $gameVersion)
    {
        $builder = new ESGatewayBuilder();

        return $builder->buildUserRepo(
            [
                'host' => $host,
                'port' => 9200,
                'index' => 'farm',
                'type' => 'user:'.$gameVersion,
            ]
        );
    }
}
