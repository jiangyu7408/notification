<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 6:25 PM.
 */

namespace Application;

use ESGateway\Factory;
use Persistency\ElasticSearch\GatewayUserPersist;
use Repository\ESGatewayUserRepo;

/**
 * Class ESGatewayBuilder.
 */
class ESGatewayBuilder
{
    /**
     * @param array $options
     *
     * @return ESGatewayUserRepo
     */
    public static function buildUserRepo(array $options)
    {
        self::validateOptions($options);

        $factory = new Factory();

        $client = $factory->makeClient($options['host'], $options['port']);

        $type = $factory->makeType($options['index'], $options['type']);

        $persist = new GatewayUserPersist($client, $type);
        $repo = new ESGatewayUserRepo($persist, $factory);

        return $repo;
    }

    /**
     * @param array $options
     */
    protected static function validateOptions(array $options)
    {
        $expectedKeys = ['host', 'port', 'index', 'type'];
        array_map(
            function ($key) use ($options) {
                if (!array_key_exists($key, $options)) {
                    throw new \InvalidArgumentException(sprintf('key[%s] not found', $key));
                }
            },
            $expectedKeys
        );
    }
}
