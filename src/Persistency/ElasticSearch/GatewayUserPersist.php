<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:11 PM.
 */
namespace Persistency\ElasticSearch;

use Elastica\Client;
use ESGateway\Type;
use Persistency\IPersistency;

/**
 * Class GatewayUserPersist.
 */
class GatewayUserPersist implements IPersistency
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var array
     */
    protected $bulk;
    /**
     * @var string
     */
    protected $snsid;
    /**
     * @var Type
     */
    protected $type;
    /** @var array */
    protected $responses;

    /**
     * @param Client $client
     * @param Type   $type
     */
    public function __construct(Client $client, Type $type)
    {
        $this->client = $client;
        $this->type = $type;

        $this->bulk = [
            'index' => $type->index,
            'type' => $type->type,
            'body' => [],
        ];
    }

    /**
     * @param string $snsid
     */
    public function setSnsid($snsid)
    {
        assert(is_string($snsid));
        $this->snsid = $snsid;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        $client = $this->client;
        $index = $client->getIndex($this->type->index);
        $type = $index->getType($this->type->type);
        $ret = $type->getDocument($this->snsid);

        $this->responses = $ret;

        if ($ret['found'] !== true) {
            return [];
        }

        return $ret['_source'];
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->bulk['body'] = [];

        foreach ($payload as $user) {
            $this->bulk['body'][] = [
                'update' => [
                    '_id' => $user['snsid'],
                ],
            ];

            $this->bulk['body'][] = [
                'doc_as_upsert' => 'true',
                'doc' => $user,
            ];
        }

        $responses = $this->client->bulk($this->bulk);

        if ($responses['errors']) {
            throw new \RuntimeException(json_encode($responses));
        }

        $this->responses = $responses;

        return true;
    }

    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }
}
