<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Queue\Construction;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Queue\Construction\AmqpConstructor;
use Qlimix\Queue\Queue\Construction\AmqpQueueOptions;
use Qlimix\Queue\Queue\Construction\Exception\ConstructorException;

final class AmqpConstructorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConstruct(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('queue_declare');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $constructor = new AmqpConstructor(
            $factory,
            new AmqpQueueOptions(true, true, false, [])
        );

        $constructor->construct('queue');
    }

    /**
     * @test
     */
    public function shouldThrowOnConnectionException(): void
    {
        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willThrowException(new Exception());

        $constructor = new AmqpConstructor(
            $factory,
            new AmqpQueueOptions(true, true, false, [])
        );

        $this->expectException(ConstructorException::class);

        $constructor->construct('queue');
    }
}
