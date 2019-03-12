<?php declare(strict_types=1);

namespace Qlimix\Queue\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\ProviderInterface;
use Qlimix\DependencyContainer\RegistryInterface;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\AmqpDefaultBatchExchange;
use Qlimix\Queue\Exchange\AmqpDefaultExchange;
use Qlimix\Queue\Exchange\AmqpFanoutBatchExchange;
use Qlimix\Queue\Exchange\AmqpFanoutExchange;

final class AMQPLibExchangeServiceProvider implements ProviderInterface
{
    /**
     * @inheritDoc
     */
    public function provide(RegistryInterface $registry): void
    {
        $registry->set(AmqpDefaultBatchExchange::class, static function (ContainerInterface $container) {
            return new AmqpDefaultBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpDefaultExchange::class, static function (ContainerInterface $container) {
            return new AmqpDefaultBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpFanoutBatchExchange::class, static function (ContainerInterface $container) {
            return new AmqpFanoutBatchExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });

        $registry->set(AmqpFanoutExchange::class, static function (ContainerInterface $container) {
            return new AmqpFanoutExchange(
                $container->get(AmqpConnectionFactory::class)
            );
        });
    }
}
