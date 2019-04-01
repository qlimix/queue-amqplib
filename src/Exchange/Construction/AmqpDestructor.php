<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Construction;

use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\Exception\DestructorException;
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
    public function destruct(string $exchange): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->exchange_delete($exchange);
        } catch (Throwable $exception) {
            throw new DestructorException('Failed to destruct exchange', 0, $exception);
        }
    }
}
