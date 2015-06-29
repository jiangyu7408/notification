<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 7:32 PM
 */

namespace Persistency\Facebook;

/**
 * Class GatewayQueue
 * @package Persistency\Facebook
 */
class GatewayQueue extends GatewayPersist
{
    /**
     * @inheritdoc
     */
    public function persist(array $payload)
    {
        if (!array_key_exists('snsid', $payload) || empty($payload['snsid'])) {
            throw new \InvalidArgumentException('snsid not found');
        }
        $snsid   = $payload['snsid'];
        $package = $this->factory->package($payload);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($package, null, '&'),
            CURLOPT_HTTPHEADER     => ['Expect:'],
            CURLOPT_URL            => $this->factory->makeUrl($snsid)
        ];
        print_r($options);

        return true;
    }
}
