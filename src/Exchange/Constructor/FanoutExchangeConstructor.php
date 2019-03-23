<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Constructor;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\Constructor\Exception\ConstructorException;
use Throwable;

final class FanoutExchangeConstructor implements ConstructorInterface
{
    /** @var AmqpConnectionFactory */
    private $connectionFactory;

    /** @var AMQPExchangeOptions */
    private $options;

    public function __construct(AmqpConnectionFactory $connectionFactory, AMQPExchangeOptions $options)
    {
        $this->connectionFactory = $connectionFactory;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function construct(string $exchange): void
    {
        try {
            $this->connectionFactory->getConnection()->channel()->exchange_declare(
                $exchange,
                AMQPExchangeType::FANOUT,
                $this->options->isPassive(),
                $this->options->isDurable(),
                $this->options->isAutoDelete(),
                false,
                false,
                $this->options->getOptions()
            );
        } catch (Throwable $exception) {
            throw new ConstructorException('Failed to create exchange', 0, $exception);
        }
    }
}
