<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Throwable;
use function count;

final class AmqpDefaultBatchExchange implements BatchExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var AmqpConnectionFactory */
    private $amqpConnectionFactory;

    /** @var AMQPChannel */
    private $channel;

    /** @var string[] */
    private $failedMessages;

    public function __construct(AmqpConnectionFactory $amqpConnectionFactory)
    {
        $this->amqpConnectionFactory = $amqpConnectionFactory;
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
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
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

        if (count($this->failedMessages) > 0) {
            $this->failedMessages = [];
            throw new UnacknowledgedException('Messages were not acknowledged by the server');
        }
    }

    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $callback = function (AMQPMessage $message): void {
                $this->failedMessages[] = $message->get('message_id');
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
