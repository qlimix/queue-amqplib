<?php declare(strict_types=1);

namespace Qlimix\Queue\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\DependencyProviderInterface;
use Qlimix\DependencyContainer\DependencyRegistryInterface;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\AmqpDefaultBatchExchange;
use Qlimix\Queue\Exchange\AmqpDefaultExchange;
use Qlimix\Queue\Exchange\AmqpFanoutBatchExchange;
use Qlimix\Queue\Exchange\AmqpFanoutExchange;

final class AMQPLibExchangeServiceProvider implements DependencyProviderInterface
{
    /**
     * @inheritDoc
     */
    public function provide(DependencyRegistryInterface $registry): void
    {
        $registry->set(AmqpDefaultBatchExchange::class, function (ContainerInterface $container) {
            return new AmqpDefaultBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpDefaultExchange::class, function (ContainerInterface $container) {
            return new AmqpDefaultBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpFanoutBatchExchange::class, function (ContainerInterface $container) {
            return new AmqpFanoutBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpFanoutExchange::class, function (ContainerInterface $container) {
            return new AmqpFanoutExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });
    }
}
