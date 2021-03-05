<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Channel\Exception\ChannelProviderException;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Callback\FailedCallback;
use Throwable;

final class BatchExchangeChannelProvider implements ChannelProviderInterface
{
    private AmqpConnectionFactoryInterface $connectionFactory;
    private FailedCallback $failedCallBack;

    private ?AMQPChannel $channel;

    public function __construct(AmqpConnectionFactoryInterface $connectionFactory, FailedCallback $failedCallBack)
    {
        $this->connectionFactory = $connectionFactory;
        $this->failedCallBack = $failedCallBack;
    }

    /**
     * @inheritDoc
     */
    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            try {
                $this->channel = $this->connectionFactory->getConnection()->channel();
                $callback = [$this->failedCallBack, 'callback'];
                $this->channel->set_nack_handler($callback);
                $this->channel->set_return_listener($callback);
                $this->channel->confirm_select();
            } catch (Throwable $exception) {
                throw new ChannelProviderException('Failed to create channel', 0, $exception);
            }
        }

        return $this->channel;
    }
}
