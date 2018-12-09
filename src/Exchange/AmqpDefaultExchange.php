<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;

final class AmqpDefaultExchange implements ExchangeInterface
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
    public function exchange(ExchangeMessage $message): void
    {
        $channel = $this->getChannel();

        $channel->basic_publish(new AMQPMessage(
                $message->getMessage(),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            ),
            null,
            $message->getRoute(),
            true
        );

        try {
            $channel->wait_for_pending_acks_returns(self::TIMEOUT);
        } catch (AMQPTimeoutException $exception) {
            throw new TimeOutException('Delivering message to exchange timed out', 0, $exception);
        }

        if ($this->nack) {
            throw new UnacknowledgedException('Message was not acknowledged by the server');
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
            $callback = function () {
                $this->nack = true;
            };
            $this->channel->set_nack_handler($callback);
            $this->channel->set_return_listener($callback);
            $this->channel->confirm_select();
        }

        return $this->channel;
    }
}
