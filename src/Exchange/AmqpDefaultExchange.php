<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Throwable;

final class AmqpDefaultExchange implements ExchangeInterface
{
    private const TIMEOUT = 1;

    /** @var ChannelProviderInterface */
    private $channelProvider;

    /** @var AmqpNegativeAcknowledgeInterface */
    private $negativeAcknowledge;

    /** @var int */
    private $deliveryMode;

    public function __construct(
        ChannelProviderInterface $channelProvider,
        AmqpNegativeAcknowledgeInterface $negativeAcknowledge,
        int $deliveryMode
    ) {
        $this->channelProvider = $channelProvider;
        $this->negativeAcknowledge = $negativeAcknowledge;
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @inheritDoc
     */
    public function exchange(ExchangeMessage $message): void
    {
        try {
            $channel = $this->channelProvider->getChannel();

            $channel->basic_publish(
                new AMQPMessage(
                    $message->getMessage(),
                    ['delivery_mode' => $this->deliveryMode]
                ),
                '',
                $message->getRoute(),
                true
            );

            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering message to exchange timed out', 0, $exception);
        } catch (Throwable $exception) {
            throw new ExchangeException('Failed to publish message to exchange', 0, $exception);
        }

        if ($this->negativeAcknowledge->has()) {
            $this->negativeAcknowledge->reset();
            throw new UnacknowledgedException('Message was not acknowledged by the server');
        }
    }
}
