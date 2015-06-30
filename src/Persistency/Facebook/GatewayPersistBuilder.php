<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:48 AM
 */

namespace Persistency\Facebook;

use FBGateway\FactoryBuilder;
use InvalidArgumentException;
use Persistency\Audit\AuditStorage;

/**
 * Class GatewayPersistBuilder
 * @package Persistency
 */
class GatewayPersistBuilder
{
    /**
     * @param array $config
     * @return null|GatewayPersist
     */
    public function build(array $config)
    {
        try {
            $fbGatewayFactory = (new FactoryBuilder())->create($config);
            $auditStorage     = new AuditStorage();
            return new GatewayQueue($fbGatewayFactory, $auditStorage);
        } catch (InvalidArgumentException $e) {
            // @todo error report
            return null;
        }
    }
}
