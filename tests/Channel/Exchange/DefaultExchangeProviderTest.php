<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Channel\Exchange;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\Exchange\DefaultExchangeProvider;
use Qlimix\Queue\Connection\AmqpConnectionFactoryInterface;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledgeInterface;
use Qlimix\Queue\Exchange\Callback\NackCallback;

final class DefaultExchangeProviderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldProvide(): void
    {
        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('set_nack_handler');

        $channel->expects($this->once())
            ->method('set_return_listener');

        $channel->expects($this->once())
            ->method('confirm_select');

        $connection = $this->createMock(AMQPStreamConnection::class);

        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $factory = $this->createMock(AmqpConnectionFactoryInterface::class);

        $factory->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $batchChannelProvider = new DefaultExchangeProvider(
            $factory,
            new NackCallback($this->createMock(AmqpNegativeAcknowledgeInterface::class))
        );

        $batchChannelProvider->getChannel();
    }
}
