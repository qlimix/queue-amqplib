<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;

final class AmqpBatchChannelConfigurator implements AmqpChannelConfiguratorInterface
{
    /** @var AmqpConnectionFactoryInterface */
    private $connectionFactory;

    /** @var AmqpMessageHolderInterface */
    private $amqpMessageHolder;

    /** @var string */
    private $queue;

    /** @var int */
    private $amount;

    /** @var AMQPChannel|null */
    private $channel;

    public function __construct(
        AmqpConnectionFactoryInterface $connectionFactory,
        AmqpMessageHolderInterface $amqpMessageHolder,
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
            $this->channel->basic_consume(
                $this->queue,
                '',
                false,
                false,
                false,
                false,
                function ($message): void {
                    $this->amqpMessageHolder->addMessage($message);
                }
            );
        }

        return $this->channel;
    }
}
