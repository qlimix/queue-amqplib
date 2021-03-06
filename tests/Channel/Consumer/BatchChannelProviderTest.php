<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Channel\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\Consumer\BatchChannelProvider;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Consumer\AmqpMessageHolder;
use Qlimix\Queue\Exchange\Callback\MessageCallback;

final class BatchChannelProviderTest extends TestCase
{
    public function testShouldProvide(): void
    {
        $fetchAmount = 10;
        $configuredQueue = 'test';
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_qos')
            ->willReturnCallback(static function ($size, $amount, $global) use ($fetchAmount) {
                return $size === 0 && $amount !== $fetchAmount && $global !== false;
            });

        $channel->expects(self::once())
            ->method('basic_consume')
            ->willReturnCallback(static function ($queue) use ($configuredQueue) {
                    return $queue === $configuredQueue;
            });

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects(self::once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);

        $batchChannelProvider = new BatchChannelProvider(
            $factory,
            new MessageCallback(new AmqpMessageHolder()),
            $configuredQueue,
            $fetchAmount
        );

        $amqpChannel = $batchChannelProvider->getChannel();

        self::assertSame($amqpChannel, $channel);
    }

    public function testShouldNotRecreateChannel(): void
    {
        $fetchAmount = 10;
        $configuredQueue = 'test';
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_qos')
            ->willReturnCallback(static function ($size, $amount, $global) use ($fetchAmount) {
                return $size === 0 && $amount !== $fetchAmount && $global !== false;
            });

        $channel->expects(self::once())
            ->method('basic_consume')
            ->willReturnCallback(static function ($queue) use ($configuredQueue) {
                    return $queue === $configuredQueue;
            });

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects(self::once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);

        $batchChannelConfigurator = new BatchChannelProvider(
            $factory,
            new MessageCallback(new AmqpMessageHolder()),
            $configuredQueue,
            $fetchAmount
        );

        $batchChannelConfigurator->getChannel();
        $amqpChannel = $batchChannelConfigurator->getChannel();

        self::assertSame($amqpChannel, $channel);
    }
}
