<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Channel\Exception\ChannelProviderException;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolderInterface;
use Qlimix\Queue\Exchange\AmqpFanoutBatchExchange;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpFanoutBatchExchangeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBatchExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns');

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->once())
            ->method('hasFailed');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }

    /**
     * @test
     */
    public function ShouldNotRecreateChannel(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->exactly(6))
            ->method('batch_basic_publish');

        $channel->expects($this->exactly(2))
            ->method('publish_batch');

        $channel->expects($this->exactly(2))
            ->method('wait_for_pending_acks_returns');

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->exactly(2))
            ->method('hasFailed');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->exactly(2))
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOnPublishException(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch')
            ->willThrowException(new Exception());

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(ExchangeException::class);

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOnTimeOutException(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(TimeOutException::class);

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOnFailedMessages(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->once())
            ->method('hasFailed')
            ->willReturn(true);

        $failedMessageHolder->expects($this->once())
            ->method('reset');

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(UnacknowledgedException::class);

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOnConnectionFailure(): void
    {
        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willThrowException(new ChannelProviderException());

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $exchange = new AmqpFanoutBatchExchange(
            $channelProvider,
            $failedMessageHolder,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(ExchangeException::class);

        $exchange->exchange([
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
            new ExchangeMessage('route', 'message'),
        ]);
    }
}
