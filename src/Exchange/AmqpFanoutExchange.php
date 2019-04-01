<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;

final class AmqpFanoutExchange implements ExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var AmqpConnectionFactoryInterface */
    private $amqpConnectionFactory;

    /** @var AmqpNegativeAcknowledgeInterface */
    private $negativeAcknowledge;

    /** @var int */
    private $deliveryMode;

    /** @var AMQPChannel */
    private $channel;

    public function __construct(
        AmqpConnectionFactoryInterface $amqpConnectionFactory,
        AmqpNegativeAcknowledgeInterface $negativeAcknowledge,
        int $deliveryMode
    ) {
        $this->amqpConnectionFactory = $amqpConnectionFactory;
        $this->negativeAcknowledge = $negativeAcknowledge;
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @inheritDoc
     */
    public function exchange(ExchangeMessage $message): void
    {
        $channel = $this->getChannel();

        $channel->basic_publish(
            new AMQPMessage(
                $message->getMessage(),
                ['delivery_mode' => $this->deliveryMode]
            ),
            $message->getRoute(),
            '',
            true
        );

        try {
            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering message to exchange timed out', 0, $exception);
        }

        if ($this->negativeAcknowledge->hasNegativeAcknowledge()) {
            $this->negativeAcknowledge->reset();
            throw new UnacknowledgedException('Message was not acknowledged by the server');
        }
    }

    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $callback = function (): void {
                $this->negativeAcknowledge->nack();
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
