<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Throwable;

final class AmqpDefaultBatchExchange implements BatchExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var AmqpConnectionFactoryInterface */
    private $amqpConnectionFactory;

    /** @var AmqpFailedMessagesHolderInterface */
    private $failedMessageHolder;

    /** @var int */
    private $deliveryMode;

    /** @var AMQPChannel */
    private $channel;

    public function __construct(
        AmqpConnectionFactoryInterface $amqpConnectionFactory,
        AmqpFailedMessagesHolderInterface $failedMessageHolder,
        int $deliveryMode
    ) {
        $this->amqpConnectionFactory = $amqpConnectionFactory;
        $this->failedMessageHolder = $failedMessageHolder;
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @inheritDoc
     */
    public function exchange(array $messages): void
    {
        $channel = $this->getChannel();

        foreach ($messages as $index => $message) {
            $channel->batch_basic_publish(
                new AMQPMessage(
                    $message->getMessage(),
                    [
                        'delivery_mode' => $this->deliveryMode,
                        'message_id' => $index,
                    ]
                ),
                '',
                $message->getRoute(),
                true
            );
        }

        try {
            $this->channel->publish_batch();
        } catch (Throwable $exception) {
            throw new ExchangeException('Failed batch publish', 0, $exception);
        }

        try {
            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering messages to exchange timed out', 0, $exception);
        }

        if ($this->failedMessageHolder->hasFailed()) {
            $this->failedMessageHolder->reset();
            throw new UnacknowledgedException('Messages were not acknowledged by the server');
        }
    }

    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $callback = function (AMQPMessage $message): void {
                $this->failedMessageHolder->fail((string) $message->get('message_id'));
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
