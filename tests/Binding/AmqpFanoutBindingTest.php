<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Binding;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Binding\AmqpFanoutBinding;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;

final class AmqpFanoutBindingTest extends TestCase
{
    public function testShouldBind(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('queue_bind')
            ->willReturn(null);

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects(self::once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);

        $binding = new AmqpFanoutBinding($factory);
        $binding->bind('exchange', 'queue');
    }

    public function testShouldThrowOnException(): void
    {
        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willThrowException(new Exception());

        $binding = new AmqpFanoutBinding($factory);

        $this->expectException(Exception::class);

        $binding->bind('exchange', 'queue');
    }
}
