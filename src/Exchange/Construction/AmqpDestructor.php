<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Construction;

use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\Construction\Exception\DestructorException;
use Throwable;

final class AmqpDestructor implements DestructorInterface
{
    /** @var AmqpConnectionFactory */
    private $connectionFactory;

    public function __construct(AmqpConnectionFactory $connectionFactory)
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
