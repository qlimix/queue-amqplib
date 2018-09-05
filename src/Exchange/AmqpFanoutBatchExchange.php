<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
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

    /** @var bool */
    private $nack;

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
        $channel = $this->getChannel();

        foreach ($envelopes as $envelope) {
            $channel->batch_basic_publish(new AMQPMessage(
                $envelope->getMessage(),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            ),
                $envelope->getRoute(),
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
            throw new TimeOutException('Delivering envelopes to exchange timed out', 0, $exception);
        }

        if ($this->nack) {
            throw new UnacknowledgedException('Envelopes were not acknowledged by the server');
        }

        $this->nack = false;
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $this->channel->set_nack_handler(function () {
                $this->nack = true;
            });
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
