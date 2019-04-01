<?php declare(strict_types=1);

namespace Qlimix\Queue\Queue\Construction;

use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Queue\Construction\Exception\DestructorException;
use Throwable;

final class AmqpDestructor implements DestructorInterface
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
    public function destruct(string $queue): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->queue_delete($queue);
        } catch (Throwable $exception) {
            throw new DestructorException('Failed to destruct exchange', 0, $exception);
        }
    }
}
