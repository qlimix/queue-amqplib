<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\AmqpDefaultBatchExchange;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolderInterface;
use Qlimix\Queue\Exchange\Exception\ExchangeException;
use Qlimix\Queue\Exchange\Exception\TimeOutException;
use Qlimix\Queue\Exchange\Exception\UnacknowledgedException;
use Qlimix\Queue\Exchange\ExchangeMessage;

final class AmqpDefaultBatchExchangeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBatchExchange(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns');

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->once())
            ->method('hasFailed');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $exchange = new AmqpDefaultBatchExchange(
            $factory,
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

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->exactly(6))
            ->method('batch_basic_publish');

        $channel->expects($this->exactly(2))
            ->method('publish_batch');

        $channel->expects($this->exactly(2))
            ->method('wait_for_pending_acks_returns');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->exactly(2))
            ->method('hasFailed');

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);


        $exchange = new AmqpDefaultBatchExchange(
            $factory,
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

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch')
            ->willThrowException(new Exception());

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $exchange = new AmqpDefaultBatchExchange(
            $factory,
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

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

        $channel->expects($this->once())
            ->method('wait_for_pending_acks_returns')
            ->willThrowException(new AMQPTimeoutException());

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $exchange = new AmqpDefaultBatchExchange(
            $factory,
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

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $channel->expects($this->exactly(3))
            ->method('batch_basic_publish');

        $channel->expects($this->once())
            ->method('publish_batch');

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

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->once())
            ->method('hasFailed')
            ->willReturn(true);

        $failedMessageHolder->expects($this->once())
            ->method('reset');

        $exchange = new AmqpDefaultBatchExchange(
            $factory,
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
}
