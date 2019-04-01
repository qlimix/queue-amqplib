<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\AmqpDefaultExchange;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledgeInterface;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpDefaultExchangeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->once())
            ->method('basic_publish');

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $negativeAcknowledge->expects($this->once())
            ->method('hasNegativeAcknowledge');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $exchange = new AmqpDefaultExchange(
            $factory,
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
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->once())
            ->method('basic_publish');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $exchange = new AmqpDefaultExchange(
            $factory,
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
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->once())
            ->method('basic_publish');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $negativeAcknowledge = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $negativeAcknowledge->expects($this->once())
            ->method('hasNegativeAcknowledge')
            ->willReturn(true);

        $negativeAcknowledge->expects($this->once())
            ->method('reset');

        $exchange = new AmqpDefaultExchange(
            $factory,
            $negativeAcknowledge,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        );

        $this->expectException(UnacknowledgedException::class);

        $exchange->exchange(new ExchangeMessage('route', 'message'));
    }
}
