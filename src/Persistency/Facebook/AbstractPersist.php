<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 4:12 PM
 */
namespace Persistency\Facebook;

use FBGateway\Factory;
use Persistency\Audit\AuditStorage;
use Persistency\IPersistency;

/**
 * Class GatewayPersist
 * @package Persistency
 */
abstract class AbstractPersist implements IPersistency
{
    /**
     * @var resource
     */
    protected $channel;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var AuditStorage
     */
    protected $audit;

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return AuditStorage
     */
    public function getAuditStorage()
    {
        return $this->audit;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        // no logical retrieve in this context.
        return [];
    }

    /**
     * @param array $payload
     * @return bool
     * @throws \InvalidArgumentException
     */
    abstract public function persist(array $payload);

    /**
     * @param mixed $response
     * @param array $payload
     */
    protected function handleError($response, array $payload)
    {
        $this->audit->persist(
            [
                'success'  => false,
                'response' => $response,
                'payload'  => $payload
            ]
        );
    }

    /**
     * @param array $payload
     */
    protected function handleSuccess(array $payload)
    {
        $this->audit->persist(
            [
                'success' => true,
                'payload' => $payload
            ]
        );
    }
}
