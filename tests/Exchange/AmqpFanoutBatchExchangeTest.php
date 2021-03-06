<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Channel\Exception\ChannelProviderException;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolder;
use Qlimix\Queue\Exchange\AmqpFanoutBatchExchange;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpFanoutBatchExchangeTest extends TestCase
{
    public function testShouldBatchExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::exactly(3))
            ->method('batch_basic_publish');

        $channel->expects(self::once())
            ->method('publish_batch');

        $channel->expects(self::once())
            ->method('wait_for_pending_acks_returns');

        $failedMessageHolder = new AmqpFailedMessagesHolder();

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
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
     *
    public function testShould
     */
    public function ShouldNotRecreateChannel(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::exactly(6))
            ->method('batch_basic_publish');

        $channel->expects(self::exactly(2))
            ->method('publish_batch');

        $channel->expects(self::exactly(2))
            ->method('wait_for_pending_acks_returns');

        $failedMessageHolder = new AmqpFailedMessagesHolder();

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::exactly(2))
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

    public function testShouldThrowOnPublishException(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::exactly(3))
            ->method('batch_basic_publish');

        $channel->expects(self::once())
            ->method('publish_batch')
            ->willThrowException(new Exception());

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = new AmqpFailedMessagesHolder();

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

    public function testShouldThrowOnTimeOutException(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::exactly(3))
            ->method('batch_basic_publish');

        $channel->expects(self::once())
            ->method('publish_batch');

        $channel->expects(self::once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = new AmqpFailedMessagesHolder();

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

    public function testShouldThrowOnFailedMessages(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::exactly(3))
            ->method('batch_basic_publish');

        $channel->expects(self::once())
            ->method('publish_batch');

        $channel->expects(self::once())
            ->method('wait_for_pending_acks_returns');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $failedMessageHolder = new AmqpFailedMessagesHolder();
        $failedMessageHolder->fail('123');

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

    public function testShouldThrowOnConnectionFailure(): void
    {
        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willThrowException(new ChannelProviderException());

        $failedMessageHolder = new AmqpFailedMessagesHolder();

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
