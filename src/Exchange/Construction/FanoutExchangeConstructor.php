<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Construction;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\Exception\ConstructorException;
use Throwable;

final class FanoutExchangeConstructor implements ConstructorInterface
{
    /** @var AmqpConnectionFactoryInterface */
    private $connectionFactory;

    /** @var AMQPExchangeOptions */
    private $options;

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
