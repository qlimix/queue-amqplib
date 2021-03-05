<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Channel\Exception\ChannelProviderException;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Callback\NackCallback;
use Throwable;

final class DefaultExchangeProvider implements ChannelProviderInterface
{
    private AmqpConnectionFactoryInterface $connectionFactory;
    private NackCallback $nackCallback;

    private ?AMQPChannel $channel = null;

    public function __construct(AmqpConnectionFactoryInterface $connectionFactory, NackCallback $nackCallback)
    {
        $this->connectionFactory = $connectionFactory;
        $this->nackCallback = $nackCallback;
    }

    /**
     * @inheritDoc
     */
    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            try {
                $this->channel = $this->connectionFactory->getConnection()->channel();
                $callback = [$this->nackCallback, 'callback'];
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
