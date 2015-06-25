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
 * Class FireGunBuilder
 * @package Persistency
 */
class FireGunBuilder
{
    /**
     * @param string $appid
     * @return FireGun
     */
    public function buildFireGun($appid)
    {
        $fbGatewayFactory = (new FBGatewayBuilder())->buildFactory($appid);
        return new FireGun($fbGatewayFactory);
    }
}
