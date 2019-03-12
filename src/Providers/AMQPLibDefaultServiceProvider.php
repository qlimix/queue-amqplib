<?php declare(strict_types=1);

namespace Qlimix\Queue\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\Exception\ProviderException;
use Qlimix\DependencyContainer\ProviderInterface;
use Qlimix\DependencyContainer\RegistryInterface;
use Qlimix\Queue\Connection\AmqpConnectionFactory;

final class AMQPLibDefaultServiceProvider implements ProviderInterface
{
    public const AMQPLIB_CONNECTION_CONFIG = 'amqplib.connection.config';

    /**
     * @inheritDoc
     */
    public function provide(RegistryInterface $registry): void
    {
        if (!$registry->has(self::AMQPLIB_CONNECTION_CONFIG)) {
            throw new ProviderException('Missing required configuration');
        }

        $registry->set(AmqpConnectionFactory::class, static function (ContainerInterface $container) {
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
