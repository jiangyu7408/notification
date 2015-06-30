<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 7:32 PM
 */

namespace Persistency\Facebook;

use FBGateway\Factory;
use Persistency\Audit\AuditStorage;
use Queue\IQueue;

/**
 * Class GatewayQueue
 * @package Persistency\Facebook
 */
class GatewayQueue extends AbstractPersist
{
    /**
     * @var IQueue
     */
    protected $queue;

    /**
     * @param IQueue $queue
     * @param Factory $factory
     * @param AuditStorage $audit
     */
    public function __construct(IQueue $queue, Factory $factory, AuditStorage $audit)
    {
        $this->queue   = $queue;
        $this->factory = $factory;
        $this->audit   = $audit;
    }

    /**
     * @param array $payload
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function persist(array $payload)
    {
        if (!isset($payload['snsid'])) {
            throw new \InvalidArgumentException('snsid not found');
        }
        $snsid   = $payload['snsid'];
        $package = $this->factory->package($payload);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($package, null, '&'),
            CURLOPT_HTTPHEADER     => ['Expect:'],
            CURLOPT_URL            => $this->factory->makeUrl($snsid)
        ];
        print_r($options);

        $ret = $this->queue->push(json_encode($options));
        xdebug_debug_zval('ret');

        return true;
    }
}
