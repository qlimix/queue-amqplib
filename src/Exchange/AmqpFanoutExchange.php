<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Envelope\EnvelopeInterface;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;

final class AmqpFanoutExchange implements ExchangeInterface
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
    public function exchange(EnvelopeInterface $envelope): void
    {
        $channel = $this->getChannel();

        $channel->basic_publish(new AMQPMessage(
                $envelope->getMessage(),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            ),
            $envelope->getRoute(),
            null,
            true
        );

        try {
            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering envelope to exchange timed out', 0, $exception);
        }

        if ($this->nack) {
            throw new UnacknowledgedException('Envelope was not acknowledged by the server');
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
