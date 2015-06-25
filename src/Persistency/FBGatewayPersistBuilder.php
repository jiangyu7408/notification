<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:48 AM
 */

namespace Persistency;

use FBGateway\FBGatewayBuilder;

/**
 * Class FBGatewayPersistBuilder
 * @package Persistency
 */
class FBGatewayPersistBuilder
{
    /**
     * @param string $appid
     * @return FBGatewayPersist
     */
    public function build($appid)
    {
        $fbGatewayFactory = (new FBGatewayBuilder())->buildFactory($appid);
        return new FBGatewayPersist($fbGatewayFactory);
    }
}
