<?php declare(strict_types=1);

namespace Qlimix\Queue\Binding;

use Qlimix\Queue\Binding\Exception\BindingException;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Throwable;

final class AmqpFanoutBinding implements BindingInterface
{
    /** @var AmqpConnectionFactoryInterface */
    private $connectionFactory;

    public function __construct(AmqpConnectionFactoryInterface $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function bind(string $exchange, string $queue): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->queue_bind($queue, $exchange);
        } catch (Throwable $exception) {
            throw new BindingException('Failed to bind queue to exchange', 0, $exception);
        }
    }
}
