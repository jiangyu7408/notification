<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:11 PM.
 */
namespace Persistency\ElasticSearch;

use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use ESGateway\Type;
use Persistency\IPersistency;

/**
 * Class GatewayUserPersist.
 */
class GatewayUserPersist implements IPersistency
{
    /** @var Client */
    protected $elasticaClient;
    /** @var Type */
    protected $target;
    /** @var string */
    protected $snsid;
    /** @var array */
    protected $responses;

    /**
     * @param Client $client
     * @param Type   $target
     */
    public function __construct(Client $client, Type $target)
    {
        $this->elasticaClient = $client;
        $this->target = $target;
    }

    /**
     * @param string $snsid
     *
     * @return static
     */
    public function setSnsid($snsid)
    {
        assert(is_string($snsid));
        $this->snsid = $snsid;

        return $this;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        $elastica = $this->elasticaClient;
        $index = $elastica->getIndex($this->target->index);
        $type = $index->getType($this->target->type);

        try {
            $document = $type->getDocument($this->snsid);
        } catch (NotFoundException $e) {
            return [];
        }

        $this->responses = $document;

        return $document->getData();
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    public function persist(array $payload)
    {
        $documents = array_map(
            function (array $user) {
                return $this->makeDocument($user);
            },
            $payload
        );
        $responseSet = $this->elasticaClient->getIndex($this->target->index)
                                            ->addDocuments($documents);
        $this->responses = $responseSet;

        return true;
    }

    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param array $user
     *
     * @return Document
     */
    protected function makeDocument(array $user)
    {
        $document = new Document($user['snsid'], $user);
        $document->setDocAsUpsert(true)
                 ->setIndex($this->target->index)
                 ->setType($this->target->type);

        return $document;
    }
}
