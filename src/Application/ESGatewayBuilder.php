<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 6:25 PM
 */

namespace Application;

use ESGateway\Factory;
use Persistency\ElasticSearch\GatewayUserPersist;
use Repository\ESGatewayUserRepo;

/**
 * Class ESGatewayBuilder
 * @package Application
 */
class ESGatewayBuilder
{
    /**
     * @param array $options
     * @return ESGatewayUserRepo
     */
    public function buildUserRepo(array $options)
    {
        $factory = new Factory();

        $dsn    = $factory->makeDsn($options['host'], $options['port']);
        $client = $factory->makeClient($dsn);

        $type = $factory->makeType($options['index'], $options['type']);

        $persist = new GatewayUserPersist($client, $type);

        $repo = new ESGatewayUserRepo($persist, $factory);

        return $repo;
    }
}
