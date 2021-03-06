<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Construction;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\Exception\ConstructorException;
use Throwable;

final class DirectExchangeConstructor implements ConstructorInterface
{
    private AmqpConnectionFactoryInterface $connectionFactory;
    private AMQPExchangeOptions $options;

    public function __construct(AmqpConnectionFactoryInterface $connectionFactory, AMQPExchangeOptions $options)
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
                AMQPExchangeType::DIRECT,
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
