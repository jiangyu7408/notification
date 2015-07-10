<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:02 AM.
 */

namespace FBGateway;

use InvalidArgumentException;

/**
 * Class FactoryBuilder.
 */
class FactoryBuilder
{
    /**
     * @param array $config
     *
     * @return Factory
     *
     * @throws InvalidArgumentException
     */
    public function create(array $config)
    {
        $param = $this->buildParam($config);

        return new Factory($param);
    }

    /**
     * @param array $config
     *
     * @return Param
     *
     * @throws InvalidArgumentException
     */
    public function buildParam(array $config)
    {
        if (!array_key_exists('appId', $config) || !array_key_exists('secretKey', $config)) {
            throw new InvalidArgumentException('bad config file');
        }
        $param = new Param();
        $param->appid = $config['appId'];
        $param->secretKey = $config['secretKey'];
        if (array_key_exists('openGraphEndpoint', $config)) {
            $param->endpoint = $config['openGraphEndpoint'];
        }

        return $param;
    }
}
