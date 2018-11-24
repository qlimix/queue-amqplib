<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Consumer\AmqpMessageHolder;

final class AmqpBatchChannelConfigurator implements AmqpChannelConfiguratorInterface
{
    /** @var AmqpConnectionFactory */
    private $connectionFactory;

    /** @var AmqpMessageHolder */
    private $amqpMessageHolder;

    /** @var string */
    private $queue;

    /** @var int */
    private $amount;

    /** @var null|AMQPChannel */
    private $channel;

    /**
     * @param AmqpConnectionFactory $connectionFactory
     * @param AmqpMessageHolder $amqpMessageHolder
     * @param string $queue
     * @param int $amount
     */
    public function __construct(
        AmqpConnectionFactory $connectionFactory,
        AmqpMessageHolder $amqpMessageHolder,
        string $queue,
        int $amount
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->amqpMessageHolder = $amqpMessageHolder;
        $this->queue = $queue;
        $this->amount = $amount;
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->connectionFactory->getConnection()->channel();
            $this->channel->basic_qos(0, $this->amount, false);
            $messageHolder = $this->amqpMessageHolder;
            $this->channel->basic_consume(
                $this->queue,
                '',
                false,
                false,
                false,
                false,
                function ($message) use ($messageHolder) {
                    $messageHolder->addMessage($message);
                }
            );
        }

        return $this->channel;
    }
}
