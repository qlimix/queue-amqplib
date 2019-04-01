<?php declare(strict_types=1);

namespace Qlimix\Queue\Queue\Construction;

use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Queue\Construction\Exception\ConstructorException;
use Throwable;

final class AmqpConstructor implements ConstructorInterface
{
    /** @var AmqpConnectionFactoryInterface */
    private $connectionFactory;

    /** @var AmqpQueueOptions */
    private $options;

    public function __construct(AmqpConnectionFactoryInterface $connectionFactory, AmqpQueueOptions $options)
    {
        $this->connectionFactory = $connectionFactory;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function construct(string $queue): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->queue_declare(
                $queue,
                $this->options->isPassive(),
                $this->options->isDurable(),
                $this->options->isAutoDelete(),
                false,
                false,
                $this->options->getOptions()
            );
        } catch (Throwable $exception) {
            throw new ConstructorException('Failed to create queue', 0, $exception);
        }
    }
}
