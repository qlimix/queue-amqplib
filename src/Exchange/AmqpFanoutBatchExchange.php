<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Envelope\EnvelopeInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;

final class AmqpFanoutBatchExchange implements BatchExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var AmqpConnectionFactory */
    private $amqpConnectionFactory;

    /** @var AMQPChannel */
    private $channel;

    /** @var AMQPMessage[] */
    private $failedMessages;

    /** @var EnvelopeInterface[] */
    private $messages;

    /**
     * @param AmqpConnectionFactory $amqpConnectionFactory
     */
    public function __construct(AmqpConnectionFactory $amqpConnectionFactory)
    {
        $this->amqpConnectionFactory = $amqpConnectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function exchange(array $messages): void
    {
        $this->messages = $messages;
        $channel = $this->getChannel();

        foreach ($messages as $index => $message) {
            $channel->batch_basic_publish(new AMQPMessage(
                $message->getMessage(),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'message_id' => $index
                ]
            ),
                $message->getRoute(),
                null,
                true
            );
        }

        try {
            $this->channel->publish_batch();
        } catch (\Throwable $exception) {
            throw new ExchangeException('Failed batch public', 0, $exception);
        }

        try {
            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering messages to exchange timed out', 0, $exception);
        }

        if (count($this->failedMessages) > 0) {
            $this->failedMessages = [];
            throw new UnacknowledgedException('Message were not acknowledged by the server');
        }
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $callback = function (AMQPMessage $message) {
                $this->failedMessages[] = $this->messages[$message->get('message_id')];
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
