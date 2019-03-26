<?php declare(strict_types=1);

namespace Qlimix\Queue\Binding;

use Qlimix\Queue\Binding\Exception\BindingException;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Throwable;

final class AmqpDirectBinding implements BindingInterface
{
    /** @var AmqpConnectionFactory */
    private $connectionFactory;

    /** @var string */
    private $routingKey;

    public function __construct(AmqpConnectionFactory $connectionFactory, string $routingKey = '')
    {
        $this->connectionFactory = $connectionFactory;
        $this->routingKey = $routingKey;
    }

    /**
     * @inheritDoc
     */
    public function bind(string $exchange, string $queue): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->queue_bind($queue, $exchange, $this->routingKey);
        } catch (Throwable $exception) {
            throw new BindingException('Failed to bind queue to exchange', 0, $exception);
        }
    }
}
