<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:02 AM
 */

namespace FBGateway;

use InvalidArgumentException;

/**
 * Class FBGatewayBuilder
 * @package Builder
 */
class FBGatewayBuilder
{
    /**
     * @param array $config
     * @return FBGatewayFactory
     * @throws InvalidArgumentException
     */
    public function buildFactory(array $config)
    {
        $param = $this->buildParam($config);
        return new FBGatewayFactory($param);
    }

    /**
     * @param array $config
     * @return FBGatewayParam
     * @throws InvalidArgumentException
     */
    public function buildParam(array $config)
    {
        if (!array_key_exists('appId', $config) || !array_key_exists('secretKey', $config)) {
            throw new InvalidArgumentException('bad config file');
        }
        $param            = new FBGatewayParam();
        $param->appid     = $config['appId'];
        $param->secretKey = $config['secretKey'];
        if (array_key_exists('openGraphEndpoint', $config)) {
            $param->endpoint = $config['openGraphEndpoint'];
        }

        return $param;
    }
}
