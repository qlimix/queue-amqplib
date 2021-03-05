<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Queue\Construction;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Queue\Construction\AmqpDestructor;
use Qlimix\Queue\Queue\Construction\Exception\DestructorException;

final class AmqpDestructorTest extends TestCase
{
    public function testShouldConstruct(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('queue_delete');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects(self::once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);

        $constructor = new AmqpDestructor($factory);

        $constructor->destruct('queue');
    }

    public function testShouldThrowOnConnectionException(): void
    {
        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willThrowException(new Exception());

        $constructor = new AmqpDestructor($factory);

        $this->expectException(DestructorException::class);

        $constructor->destruct('queue');
    }
}
