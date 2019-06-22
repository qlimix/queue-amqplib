<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Connection\Exception\ConnectionException;
use Qlimix\Queue\Exchange\AmqpFanoutExchange;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledgeInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpFanoutExchangeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_publish');

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $negativeAcknowledge->expects($this->once())
            ->method('has');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    /**
     * @test
     */
    public function shouldThrowOnTimeout(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_publish');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(TimeOutException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    /**
     * @test
     */
    public function shouldThrowOnNegativeAcknowledge(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_publish');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns');

        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $negativeAcknowledge->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $negativeAcknowledge->expects($this->once())
            ->method('reset');

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(UnacknowledgedException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }

    /**
     * @test
     */
    public function shouldThrowOnConnectionFailure(): void
    {
        $channelProvider = $this->createMock(ChannelProviderInterface::class);

        $channelProvider->expects($this->once())
            ->method('getChannel')
            ->willThrowException(new ConnectionException());

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $exchange = new AmqpFanoutExchange(
            $channelProvider,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(ExchangeException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }
}
