<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 10:55 AM
 */

namespace FBGateway;

/**
 * Class FBGatewayFactory
 * @package BusinessEntity
 */
class FBGatewayFactory
{
    public function __construct(FBGatewayParam $param)
    {
        $this->param = $param;

        $this->accessToken = "{$param->appid}|{$param->secretKey}";
    }

    public function package(array $payload)
    {
        $package = [
            'access_token' => $this->accessToken,
            'href'         => '?ref_notif=' . $payload['trackRef'],
            'template'     => $payload['template'],
        ];

        return $package;
    }

    /**
     * @param string $snsid
     * @return string
     */
    public function makeUrl($snsid)
    {
        return str_replace('UID', $snsid, $this->param->endpoint);
    }
}
