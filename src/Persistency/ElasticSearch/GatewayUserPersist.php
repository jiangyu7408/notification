<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:11 PM
 */

namespace Persistency\ElasticSearch;

use Elasticsearch\Client;
use ESGateway\Type;
use Persistency\IPersistency;

/**
 * Class GatewayUserPersist
 * @package Persistency\ElasticSearch
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
     * @var array
     */
    protected $responses;
    /**
     * @var Type
     */
    protected $type;

    /**
     * @param Client $client
     * @param Type $type
     */
    public function __construct(Client $client, Type $type)
    {
        $this->client = $client;
        $this->type   = $type;

        $this->bulk = [
            'index' => $type->index,
            'type'  => $type->type,
            'body'  => []
        ];
    }

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
        $ret = $this->client->get([
            'index' => $this->type->index,
            'type'  => $this->type->type,
            'id'    => $this->snsid
        ]);

        $this->responses = $ret;

        if ($ret['found'] !== true) {
            return [];
        }

        return $ret['_source'];
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        foreach ($payload as $user) {
            $this->bulk['body'][] = [
                'update' => [
                    '_id' => $user['snsid'],
                ],
            ];

            $this->bulk['body'][] = [
                'doc_as_upsert' => 'true',
                'doc'           => $user,
            ];
        }

        $this->responses = $this->client->bulk($this->bulk);

        if ($this->responses['errors']) {
            return false;
        }

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