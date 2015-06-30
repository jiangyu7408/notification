<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:58 PM
 */

namespace Persistency\Facebook;

use FBGateway\Factory;
use Persistency\Audit\AuditStorage;

/**
 * Class GatewayPersist
 * @package Persistency
 */
class GatewayPersist extends AbstractPersist
{
    public function __construct(Factory $factory, AuditStorage $audit)
    {
        $this->factory = $factory;
        $this->audit   = $audit;
    }

    /**
     * @param array $payload
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function persist(array $payload)
    {
        if (!array_key_exists('snsid', $payload) || empty($payload['snsid'])) {
            throw new \InvalidArgumentException('snsid not found');
        }
        $snsid   = $payload['snsid'];
        $package = $this->factory->package($payload);

        $this->channel = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($package, null, '&'),
            CURLOPT_HTTPHEADER => ['Expect:'],
            CURLOPT_URL            => $this->factory->makeUrl($snsid)
        ];
        curl_setopt_array($this->channel, $options);

        $response = curl_exec($this->channel);
        curl_close($this->channel);

        if ($response === false) {
            $this->handleError($response, $payload);
            return false;
        }

        $responseArray = json_decode($response, true);
        if (!is_array($responseArray) || array_key_exists('error', $responseArray)) {
            $this->handleError($response, $payload);
            return false;
        }

        $this->handleSuccess($payload);
        return true;
    }
}
