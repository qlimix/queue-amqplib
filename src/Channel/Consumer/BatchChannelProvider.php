<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Callback\MessageCallback;

final class BatchChannelProvider implements ChannelProviderInterface
{
    private AmqpConnectionFactoryInterface $connectionFactory;
    private MessageCallback $messageCallback;
    private string $queue;
    private int $amount;

    private ?AMQPChannel $channel = null;

    public function __construct(
        AmqpConnectionFactoryInterface $connectionFactory,
        MessageCallback $messageCallback,
        string $queue,
        int $amount
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->messageCallback = $messageCallback;
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
                [$this->messageCallback, 'callback']
            );
        }

        return $this->channel;
    }
}
