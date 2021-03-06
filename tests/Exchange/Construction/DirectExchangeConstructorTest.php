<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Construction;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\AMQPExchangeOptions;
use Qlimix\Queue\Exchange\Construction\DirectExchangeConstructor;
use Qlimix\Queue\Exchange\Construction\Exception\ConstructorException;

final class DirectExchangeConstructorTest extends TestCase
{
    public function testShouldConstruct(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('exchange_declare');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects(self::once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);

        $constructor = new DirectExchangeConstructor(
            $factory,
            new AMQPExchangeOptions(true, true, false, [])
        );

        $constructor->construct('exchange');
    }

    public function testShouldThrowOnConnectionException(): void
    {
        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects(self::once())
            ->method('getConnection')
            ->willThrowException(new Exception());

        $constructor = new DirectExchangeConstructor(
            $factory,
            new AMQPExchangeOptions(true, true, false, [])
        );

        $this->expectException(ConstructorException::class);

        $constructor->construct('exchange');
    }
}
