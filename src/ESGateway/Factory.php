<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:58 PM.
 */
namespace ESGateway;

use Elastica\Client;
use Elastica\Document;
use Facade\ElasticSearch\DocumentFactory;

require CONFIG_DIR.'/library/ip2cc.php';

/**
 * Class Factory.
 */
class Factory
{
    /** @var DocumentFactory */
    protected $documentFactory;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $docPrototype = new Document();
        $docPrototype->setIndex(ELASTIC_SEARCH_INDEX)->setType('user:unknown');
        $this->documentFactory = new DocumentFactory($docPrototype);
    }

    /**
     * @param string $host
     * @param int    $port
     *
     * @return Client
     */
    public function makeClient($host, $port)
    {
        return new Client(
            [
                'host' => $host,
                'port' => $port,
            ]
        );
    }

    /**
     * @param string $index
     * @param string $typeName
     *
     * @return Type
     */
    public function makeType($index, $typeName)
    {
        assert(is_string($index) && strlen($index) > 0);
        assert(is_string($typeName) && strlen($typeName) > 0);

        $type = new Type($index, $typeName);

        return $type;
    }

    /**
     * @param array $dbEntity
     *
     * @return User
     */
    public function makeUser(array $dbEntity)
    {
        $payload = $this->documentFactory->buildPayload($dbEntity);
        $user = new User();
        foreach ($payload as $field => $value) {
            $user->{$field} = $value;
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function toArray(User $user)
    {
        return $this->documentFactory->buildPayload(get_object_vars($user));
    }
}
