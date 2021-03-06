<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Connection\Exception\ConnectionException;
use Qlimix\Queue\Exchange\AmqpFanoutExchange;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledge;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpFanoutExchangeTest extends TestCase
{
    public function testShouldExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_publish');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            new AmqpNegativeAcknowledge(),
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    public function testShouldThrowOnTimeout(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_publish');

        $channel->expects(self::once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            new AmqpNegativeAcknowledge(),
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(TimeOutException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    public function testShouldThrowOnNegativeAcknowledge(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_publish');

        $channel->expects(self::once())
            ->method('wait_for_pending_acks_returns');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $negativeAcknowledge = new AmqpNegativeAcknowledge();
        $negativeAcknowledge->nack();

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(UnacknowledgedException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    public function testShouldThrowOnConnectionFailure(): void
    {
        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects(self::once())
            ->method('getChannel')
            ->willThrowException(new ConnectionException());

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            new AmqpNegativeAcknowledge(),
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(ExchangeException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }
}
