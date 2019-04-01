<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Construction;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\Construction\AMQPExchangeOptions;
use Qlimix\Queue\Exchange\Construction\FanoutExchangeConstructor;
use Qlimix\Queue\Exchange\Construction\Exception\ConstructorException;

final class FanoutExchangeConstructorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConstruct(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('exchange_declare');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $constructor = new FanoutExchangeConstructor(
            $factory,
            new AMQPExchangeOptions(true, true, false, [])
        );

        $constructor->construct('exchange');
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

        $constructor = new FanoutExchangeConstructor(
            $factory,
            new AMQPExchangeOptions(true, true, false, [])
        );

        $this->expectException(ConstructorException::class);

        $constructor->construct('exchange');
    }
}
