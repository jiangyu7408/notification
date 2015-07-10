<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:48 AM.
 */

namespace Persistency\Facebook;

use FBGateway\FactoryBuilder as FBGatewayFactoryBuilder;
use InvalidArgumentException;
use Persistency\Audit\AuditStorage;
use Persistency\IPersistency;
use Queue\FileQueue;

/**
 * Class GatewayPersistBuilder.
 */
class GatewayPersistBuilder
{
    /**
     * @param array $config
     *
     * @return null|IPersistency
     *
     * @throws InvalidArgumentException
     */
    public function build(array $config)
    {
        if (!isset($config['queueLocation'])) {
            throw new InvalidArgumentException('queueLocation not found in config: '.print_r($config, true));
        }

        try {
            $fbGatewayFactory = (new FBGatewayFactoryBuilder())->create($config);
            $auditStorage = new AuditStorage();
            $queue = new FileQueue($config['queueLocation']);

            return new GatewayQueue($queue, $fbGatewayFactory, $auditStorage);
//            return new GatewayPersist($fbGatewayFactory, $auditStorage);
        } catch (InvalidArgumentException $e) {
            // @todo error report
            print_r($e);
            throw $e;
        }
    }
}
