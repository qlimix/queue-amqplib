<?php declare(strict_types=1);

namespace Qlimix\Providers\QueueAMQPLib;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\DependencyProviderInterface;
use Qlimix\DependencyContainer\DependencyRegistryInterface;
use Qlimix\DependencyContainer\Exception\DependencyProviderException;
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

        });

        $registry->set(AmqpDefaultExchange::class, function (ContainerInterface $container) {

        });

        $registry->set(AmqpFanoutBatchExchange::class, function (ContainerInterface $container) {

        });

        $registry->set(AmqpFanoutExchange::class, function (ContainerInterface $container) {

        });
    }
}
