<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:02 AM
 */

namespace FBGateway;

/**
 * Class FBGatewayBuilder
 * @package Builder
 */
class FBGatewayBuilder
{
    /**
     * @param string $appid
     * @return FBGatewayFactory
     */
    public function buildFactory($appid)
    {
        $param   = $this->buildParam($appid);
        $factory = new FBGatewayFactory($param);
        return $factory;
    }

    /**
     * @param string $appid
     * @return FBGatewayParam
     */
    public function buildParam($appid)
    {
        $param        = new FBGatewayParam();
        $param->appid = $appid;

        return $this->injectConfig($param);
    }

    /**
     * @param FBGatewayParam $param
     * @return FBGatewayParam
     */
    protected function injectConfig(FBGatewayParam $param)
    {
        $param->secretKey = 'secretKey from facebook.php';
        return $param;
    }
}