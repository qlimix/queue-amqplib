<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Construction;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\AmqpDestructor;
use Qlimix\Queue\Exchange\Construction\Exception\DestructorException;

final class AmqpDestructorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDestroy(): void
    {
        $toDestroyExchange = 'exchange';

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('exchange_delete')
            ->willReturnCallback(static function (string $exchange) use ($toDestroyExchange) {
                return $exchange === $toDestroyExchange;
            });

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $destructor = new AmqpDestructor($factory);

        $destructor->destruct($toDestroyExchange);
    }

    /**
     * @test
     */
    public function shouldThrowOnConnectionException(): void
    {
        $toDestroyExchange = 'exchange';

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willThrowException(new Exception());

        $destructor = new AmqpDestructor($factory);

        $this->expectException(DestructorException::class);

        $destructor->destruct($toDestroyExchange);
    }
}
