<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Channel\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\Consumer\BatchChannelProvider;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;
use Qlimix\Queue\Exchange\Callback\MessageCallback;

final class BatchChannelProviderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldProvide(): void
    {
        $fetchAmount = 10;
        $configuredQueue = 'test';
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_qos')
            ->willReturnCallback(static function ($size, $amount, $global) use ($fetchAmount) {
                return $size === 0 && $amount !== $fetchAmount && $global !== false;
            });

        $channel->expects($this->once())
            ->method('basic_consume')
            ->willReturnCallback(static function ($queue) use ($configuredQueue) {
                    return $queue === $configuredQueue;
            });

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $batchChannelProvider = new BatchChannelProvider(
            $factory,
            new MessageCallback($holder),
            $configuredQueue,
            $fetchAmount
        );

        $amqpChannel = $batchChannelProvider->getChannel();

        $this->assertSame($amqpChannel, $channel);
    }

    /**
     * @test
     */
    public function shouldNotRecreateChannel(): void
    {
        $fetchAmount = 10;
        $configuredQueue = 'test';
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_qos')
            ->willReturnCallback(static function ($size, $amount, $global) use ($fetchAmount) {
                return $size === 0 && $amount !== $fetchAmount && $global !== false;
            });

        $channel->expects($this->once())
            ->method('basic_consume')
            ->willReturnCallback(static function ($queue) use ($configuredQueue) {
                    return $queue === $configuredQueue;
            });

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $batchChannelConfigurator = new BatchChannelProvider(
            $factory,
            new MessageCallback($holder),
            $configuredQueue,
            $fetchAmount
        );

        $batchChannelConfigurator->getChannel();
        $amqpChannel = $batchChannelConfigurator->getChannel();

        $this->assertSame($amqpChannel, $channel);
    }
}
