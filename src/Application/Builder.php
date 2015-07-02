<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/02
 * Time: 7:14 PM
 */

namespace Application;

use Config\RedisConfig;
use Config\RedisConfigFactory;
use Config\RedisQueueConfig;
use Config\RedisQueueConfigFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class Builder
 * @package Application
 */
class Builder
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @param array $configFileSource
     */
    public function __construct(array $configFileSource)
    {
        $this->container = new ContainerBuilder();
        $this->setParams($configFileSource);
    }

    protected function setParams(array $configFileSource)
    {
        foreach ($configFileSource as $name => $config) {
            $this->container->setParameter($name, $config);
        }
    }

    public function create()
    {
        $this->buildRedis();
        $this->buildRedisQueue();

        return $this->container;
    }

    protected function buildRedis()
    {
        $container = $this->container;

        $factory = new RedisConfigFactory();

        $container->setDefinition(RedisConfigFactory::class, (new Definition())->setSynthetic(true));
        $container->set(RedisConfigFactory::class, $factory);
        $container->setDefinition(RedisConfig::class, (new Definition())->setSynthetic(true));
        $container->set(RedisConfig::class, $factory->create($container->getParameter('redis')));
    }

    protected function buildRedisQueue()
    {
        $container = $this->container;

        $factory = new RedisQueueConfigFactory();

        $container->setDefinition(RedisQueueConfigFactory::class, (new Definition())->setSynthetic(true));
        $container->set(RedisQueueConfigFactory::class, $factory);
        $container->setDefinition(RedisQueueConfig::class, (new Definition())->setSynthetic(true));
        $container->set(RedisQueueConfig::class, $factory->create($container->getParameter('redisQueue')));
    }
}
