<?php

namespace Okvpn\Bundle\RedisQueueBundle\DependencyInjection;

use Okvpn\Bundle\RedisQueueBundle\Consumption\LockFactory;
use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManager;
use Okvpn\Bundle\RedisQueueBundle\EventListener\LockerListener;
use Okvpn\Bundle\RedisQueueBundle\Extension\LockerExtension;
use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\RedisConnection;

use Oro\Component\MessageQueue\DependencyInjection\TransportFactoryInterface;

use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RedisTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = 'redis')
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('dsn')
                    ->defaultValue('redis://@127.0.0.1:6379/0')
                    ->validate()
                        ->ifTrue(
                            function ($dsn) {
                                $parsed = new RedisDsn($dsn);
                                return !$parsed->isValid();
                            }
                        )
                        ->thenInvalid('The redis DSN %s is invalid.')
                    ->end()
                    ->cannotBeEmpty()
                ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function createService(ContainerBuilder $container, array $config)
    {
        $this->createOptionalService($container, $config);

        $connection = new Definition(
            RedisConnection::class,
            [$config, new Reference('okvpn_redis_queue.consumption.lock_manager')]
        );

        $connection->setFactory([RedisConnection::class, 'createConnection']);
        $connectionId = sprintf('oro_message_queue.transport.%s.connection', $this->getName());
        $container->setDefinition($connectionId, $connection);
        
        return $connectionId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function createOptionalService(ContainerBuilder $container, array $config)
    {
        $container->register('okvpn_redis_queue.consumption.lock_factory', LockFactory::class)
            ->addArgument($config['dsn'])
            ->setPublic(false);

        $container->register('okvpn_redis_queue.consumption.lock_manager', LockManager::class)
            ->setFactory([
                new Reference('okvpn_redis_queue.consumption.lock_factory'),
                'create'
            ])
            ->setPublic(false);

        $container->register('okvpn_redis_queue.lisener.lock_process', LockerListener::class)
            ->addTag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'lockProcess'])
            ->addTag('kernel.event_listener', ['event' => 'console.command', 'method' => 'lockProcess'])
            ->addArgument(new Reference('okvpn_redis_queue.consumption.lock_manager'));

        $container->register('okvpn_redis_queue.extension.locker', LockerExtension::class)
            ->addArgument(new Reference('okvpn_redis_queue.consumption.lock_manager'))
            ->addTag('oro_message_queue.consumption.extension', ['priority' => 50])
            ->setPublic(false);
    }
}
