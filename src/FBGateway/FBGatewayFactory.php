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
        $this->accessToken = "{$param->appid}|{$param->secretKey}";
        $this->param       = $param;
    }

    public function package(array $payload)
    {
        $package = array(
            'template'     => $payload['template'],
            'href'         => '?ref_notif=' . $payload['trackRef'],
            'access_token' => $this->accessToken,
        );

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
