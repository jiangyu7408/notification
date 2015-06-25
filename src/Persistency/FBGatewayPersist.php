<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:58 PM
 */

namespace Persistency;

use FBGateway\FBGatewayFactory;

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

    public function __construct(FBGatewayFactory $factory)
    {
        $this->factory = $factory;
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

//        $response = curl_exec($this->channel);
        $response = '{"success":true}';
        curl_close($this->channel);

        $responseArray = json_decode($response, true);
        if (!is_array($responseArray)) {
            $this->handleError($response, $payload);
        }

        $this->handleSuccess($payload);
    }

    /**
     * @param mixed $response
     * @param array $payload
     */
    private function handleError($response, array $payload)
    {
        error_log('failed with [' . json_encode($response) . ']: ' . json_encode($payload));
    }

    /**
     * @param array $payload
     */
    private function handleSuccess(array $payload)
    {
        error_log('success: ' . json_encode($payload));
    }
}
