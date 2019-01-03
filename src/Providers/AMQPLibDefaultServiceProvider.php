<?php declare(strict_types=1);

namespace Qlimix\Queue\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\DependencyProviderInterface;
use Qlimix\DependencyContainer\DependencyRegistryInterface;
use Qlimix\DependencyContainer\Exception\DependencyProviderException;
use Qlimix\Queue\Connection\AmqpConnectionFactory;

final class AMQPLibDefaultServiceProvider implements DependencyProviderInterface
{
    public const AMQPLIB_CONNECTION_CONFIG = 'amqplib.connection.config';

    /**
     * @inheritDoc
     */
    public function provide(DependencyRegistryInterface $registry): void
    {
        if (!$registry->has(self::AMQPLIB_CONNECTION_CONFIG)) {
            throw new DependencyProviderException('Missing required configuration');
        }

        $registry->set(AmqpConnectionFactory::class, function (ContainerInterface $container) {
            $config = $container->get(self::AMQPLIB_CONNECTION_CONFIG);
            return new AmqpConnectionFactory(
                $config['host'],
                $config['port'],
                $config['vhost'],
                $config['user'],
                $config['password']
            );
        });
    }
}
