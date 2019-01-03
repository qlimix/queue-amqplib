<?php declare(strict_types=1);

namespace Qlimix\Queue\Providers\QueueAMQPLib;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\DependencyProviderInterface;
use Qlimix\DependencyContainer\DependencyRegistryInterface;
use Qlimix\Queue\Channel\AmqpBatchChannelConfigurator;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Consumer\AmqpMessageFetcher;
use Qlimix\Queue\Consumer\AmqpMessageHolder;
use Qlimix\Queue\Consumer\AmqpQueueConsumer;
use Qlimix\Queue\Consumer\QueueConsumerInterface;

final class AMQPLibConsumerServiceProvider implements DependencyProviderInterface
{
    /**
     * @inheritDoc
     */
    public function provide(DependencyRegistryInterface $registry): void
    {
        $registry->set(AmqpMessageHolder::class, function (ContainerInterface $container) {
            return new AmqpMessageHolder();
        });

        $registry->set(AmqpBatchChannelConfigurator::class, function (ContainerInterface $container) {
            return new AmqpBatchChannelConfigurator(
                $container->get(AmqpConnectionFactory::class),
                $container->get(AmqpMessageHolder::class),
                $container->get('queue'),
                $container->get('amount')
            );
        });

        $registry->set(AmqpMessageFetcher::class, function (ContainerInterface $container) {
           return new AmqpMessageFetcher(
               $container->get(AmqpBatchChannelConfigurator::class),
               $container->get(AmqpMessageHolder::class)
           );
        });

        $registry->set(QueueConsumerInterface::class, function (ContainerInterface $container) {
            return new AmqpQueueConsumer(
                $container->get(AmqpMessageFetcher::class)
            );
        });
    }
}
