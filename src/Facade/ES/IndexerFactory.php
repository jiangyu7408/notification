<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 15:11.
 */
namespace Facade\ES;

use Application\ESGatewayBuilder;

/**
 * Class IndexerFactory.
 */
class IndexerFactory extends Indexer
{
    /** @var Indexer */
    protected static $instance;

    /**
     * @param string $esHost
     * @param string $gameVersion
     * @param int    $batchSize
     *
     * @return Indexer
     */
    public static function make($esHost, $gameVersion, $batchSize = 200)
    {
        if (self::$instance === null) {
            $config = [
                'host' => $esHost,
                'port' => ELASTIC_SEARCH_PORT,
                'index' => ELASTIC_SEARCH_INDEX,
                'type' => 'user:'.$gameVersion,
            ];
            $repo = (new ESGatewayBuilder())->buildUserRepo($config);

            self::$instance = new Indexer($repo, $batchSize);
        }

        return self::$instance;
    }
}
