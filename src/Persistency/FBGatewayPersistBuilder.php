<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:48 AM
 */

namespace Persistency;

use FBGateway\FBGatewayBuilder;
use InvalidArgumentException;
use Persistency\Audit\AuditStorage;

/**
 * Class FBGatewayPersistBuilder
 * @package Persistency
 */
class FBGatewayPersistBuilder
{
    /**
     * @param array $config
     * @return null|FBGatewayPersist
     */
    public function build(array $config)
    {
        try {
            $fbGatewayFactory = (new FBGatewayBuilder())->buildFactory($config);
            $auditStorage     = new AuditStorage();
            return new FBGatewayPersist($fbGatewayFactory, $auditStorage);
        } catch (InvalidArgumentException $e) {
            // @todo error report
            return null;
        }
    }
}
