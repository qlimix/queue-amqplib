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

final class AmqpDefaultBatchExchange implements BatchExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var AmqpConnectionFactory */
    private $amqpConnectionFactory;

    /** @var AMQPChannel */
    private $channel;

    /** @var AMQPMessage[] */
    private $failedMessages;

    /** @var EnvelopeInterface[] */
    private $envelopes;

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
    public function exchange(array $envelopes): void
    {
        $this->envelopes = $envelopes;
        $channel = $this->getChannel();

        foreach ($this->envelopes as $index => $envelope) {
            $channel->batch_basic_publish(new AMQPMessage(
                    $envelope->getMessage(),
                    [
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                        'message_id' => $index
                    ]
                ),
                null,
                $envelope->getRoute(),
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
            throw new TimeOutException('Delivering envelopes to exchange timed out', 0, $exception);
        }

        if (count($this->failedMessages) > 0) {
            $this->failedMessages = [];
            throw new UnacknowledgedException('Envelopes were not acknowledged by the server');
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
                $this->failedMessages[] = $this->envelopes[$message->get('message_id')];
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
