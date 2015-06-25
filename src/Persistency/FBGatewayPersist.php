<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:58 PM
 */

namespace Persistency;

use FBGateway\FBGatewayFactory;
use Persistency\Audit\AuditStorage;

/**
 * Class FBGatewayPersist
 * @package Persistency
 */
class FBGatewayPersist implements IPersistency
{
    /**
     * @var resource
     */
    protected $channel;
    /**
     * @var FBGatewayFactory
     */
    protected $factory;
    /**
     * @var AuditStorage
     */
    protected $audit;

    public function __construct(FBGatewayFactory $factory, AuditStorage $audit)
    {
        $this->factory = $factory;
        $this->audit   = $audit;
    }

    /**
     * @return FBGatewayFactory
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
        return array();
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        $snsid   = $payload['snsid'];
        $package = $this->factory->package($payload);

        $this->channel = curl_init();

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($package, null, '&'),
            CURLOPT_HTTPHEADER     => array('Expect:'),
            CURLOPT_URL            => $this->factory->makeUrl($snsid)
        );
        curl_setopt_array($this->channel, $options);

        $response = curl_exec($this->channel);
        curl_close($this->channel);

        $responseArray = json_decode($response, true);
        if (!is_array($responseArray) || array_key_exists('error', $responseArray)) {
            $this->handleError($response, $payload);
            return false;
        }

        $this->handleSuccess($payload);
        return true;
    }

    /**
     * @param mixed $response
     * @param array $payload
     */
    private function handleError($response, array $payload)
    {
        $this->audit->persist(array(
            'success'  => false,
            'response' => $response,
            'payload'  => $payload
        ));
    }

    /**
     * @param array $payload
     */
    private function handleSuccess(array $payload)
    {
        $this->audit->persist(array(
            'success' => true,
            'payload' => $payload
        ));
    }
}
