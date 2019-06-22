<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Throwable;

final class AmqpDefaultBatchExchange implements BatchExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var ChannelProviderInterface */
    private $channelProvider;

    /** @var AmqpFailedMessagesHolderInterface */
    private $failedMessageHolder;

    /** @var int */
    private $deliveryMode;

    public function __construct(
        ChannelProviderInterface $channelProvider,
        AmqpFailedMessagesHolderInterface $failedMessageHolder,
        int $deliveryMode
    ) {
        $this->channelProvider = $channelProvider;
        $this->failedMessageHolder = $failedMessageHolder;
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @inheritDoc
     */
    public function exchange(array $messages): void
    {
        try {
            $channel = $this->channelProvider->getChannel();

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

            $channel->publish_batch();
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
}
